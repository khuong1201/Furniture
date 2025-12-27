<?php

declare(strict_types=1);

namespace Modules\Product\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;

use Modules\Shared\Services\BaseService;
use Modules\Shared\Exceptions\BusinessException;
use Modules\Shared\Contracts\InventoryServiceInterface;
use Modules\Shared\Contracts\StorageServiceInterface;

use Modules\Category\Domain\Models\Category;
use Modules\Brand\Domain\Models\Brand;
use Modules\Product\Domain\Models\Attribute;
use Modules\Product\Domain\Models\AttributeValue;
use Modules\Product\Domain\Models\Product;
use Modules\Product\Domain\Models\ProductImage;
use Modules\Product\Domain\Models\ProductVariant;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;

class ProductService extends BaseService
{
    public function __construct(
        ProductRepositoryInterface $repo,
        protected StorageServiceInterface $storageService,
        protected InventoryServiceInterface $inventoryService
    ) {
        parent::__construct($repo);
    }

    public function create(array $data): Model
    {
        if (!empty($data['variants'])) {
            $this->validateUniqueSkuInPayload($data['variants']);
        }

        return DB::transaction(function () use ($data) {
            $productData = [
                'name'         => $data['name'],
                'slug'         => $data['slug'] ?? Str::slug($data['name']),
                'description'  => $data['description'] ?? null,
                'is_active'    => $data['is_active'] ?? true,
                'has_variants' => $data['has_variants'] ?? false,
                'price'        => $data['has_variants'] ? 0 : ($data['price'] ?? 0),
                'sku'          => $data['has_variants'] ? null : ($data['sku'] ?? null),
            ];

            if (isset($data['category_uuid'])) {
                $productData['category_id'] = Category::where('uuid', $data['category_uuid'])->value('id');
            }
            if (isset($data['brand_uuid'])) {
                $productData['brand_id'] = Brand::where('uuid', $data['brand_uuid'])->value('id');
            }

            $product = parent::create($productData);

            if ($product->has_variants && !empty($data['variants'])) {
                $this->processVariants($product, $data['variants']);
            } else {
                $this->createSingleVariant($product, $data);
            }

            if (!empty($data['images'])) {
                $this->processImages($product, $data['images']);
            }

            $this->updateProductMetadata($product);

            return $product->load(['variants', 'images', 'brand', 'category']);
        });
    }

    public function update(string $uuid, array $data): Model
    {
        $product = $this->findByUuidOrFail($uuid);

        if (isset($data['variants'])) {
            $this->validateUniqueSkuInPayload($data['variants']);
        }

        return DB::transaction(function () use ($product, $data) {
            $updateData = collect($data)->only(['name', 'description', 'is_active', 'slug'])->toArray();
            
            if (isset($data['name']) && !isset($data['slug'])) {
                $updateData['slug'] = Str::slug($data['name']);
            }
            if (isset($data['category_uuid'])) {
                $updateData['category_id'] = Category::where('uuid', $data['category_uuid'])->value('id');
            }
            if (isset($data['brand_uuid'])) {
                $updateData['brand_id'] = Brand::where('uuid', $data['brand_uuid'])->value('id');
            }

            if (!$product->has_variants) {
                if (isset($data['price'])) $updateData['price'] = $data['price'];
                if (isset($data['sku'])) $updateData['sku'] = $data['sku'];
            }

            $product->update($updateData);

            if ($product->has_variants && isset($data['variants'])) {
                $product->variants()->delete(); 
                $this->processVariants($product, $data['variants']);
            } 
            elseif (!$product->has_variants && isset($data['warehouse_stock'])) {
                $variant = $product->variants()->first();
                if ($variant) {
                    $this->syncVariantStock($variant->id, $data['warehouse_stock']);
                } else {
                    $this->createSingleVariant($product, $data);
                }
            }

            if (isset($data['deleted_image_uuids']) && is_array($data['deleted_image_uuids'])) {
                $this->deleteImages($data['deleted_image_uuids']);
            }
            if (isset($data['current_images_state']) && is_array($data['current_images_state'])) {
                $this->updateImagesState($data['current_images_state']);
            }

            $this->updateProductMetadata($product);

            return $product->load(['variants', 'images']);
        });
    }

    protected function createSingleVariant(Product $product, array $data): void
    {
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku'        => $data['sku'] ?? $product->sku,
            'name'       => $product->name,
            'price'      => $data['price'] ?? 0,
            'weight'     => $data['weight'] ?? 0,
        ]);

        $stockData = $data['warehouse_stock'] ?? $data['stock'] ?? [];
        
        if (!empty($stockData)) {
            $this->syncVariantStock($variant->id, $stockData);
        }
    }

    protected function processVariants(Product $product, array $variantsData): void
    {
        $allAttrSlugs = [];
        foreach ($variantsData as $v) {
            if (!empty($v['attributes'])) {
                foreach ($v['attributes'] as $attr) {
                    $allAttrSlugs[] = $attr['attribute_slug'];
                }
            }
        }
        $allAttrSlugs = array_unique($allAttrSlugs);
        $attributesMap = Attribute::whereIn('slug', $allAttrSlugs)->get()->keyBy('slug');

        foreach ($variantsData as $vData) {
            $variantName = $vData['name'] ?? ($product->name . ' - ' . $vData['sku']);

            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'sku'        => $vData['sku'],
                'name'       => $variantName,
                'price'      => $vData['price'],
                'weight'     => $vData['weight'] ?? 0,
            ]);

            if (!empty($vData['attributes'])) {
                $this->attachAttributesToVariant($variant, $vData['attributes'], $attributesMap);
            }

            $stockData = $vData['warehouse_stock'] ?? $vData['stock'] ?? [];
            if (!empty($stockData)) {
                $this->syncVariantStock($variant->id, $stockData);
            }
        }
    }

    /**
     * FIX: Revert logic map ID.
     * Chuyển lại về việc gửi payload thô (có chứa warehouse_uuid)
     * để InventoryService tự xử lý lookup.
     */
    protected function syncVariantStock(int $variantId, array $stockData): void
    {
        $cleanPayload = [];

        foreach ($stockData as $item) {
            // Chỉ cần đảm bảo có đủ key, không map sang ID nữa vì InventoryService cần UUID
            if (isset($item['warehouse_uuid']) && isset($item['quantity'])) {
                $cleanPayload[] = [
                    'warehouse_uuid' => $item['warehouse_uuid'],
                    'quantity'       => (int)$item['quantity']
                ];
            }
        }

        if (!empty($cleanPayload)) {
            $this->inventoryService->syncStock($variantId, $cleanPayload);
        }
    }

    protected function attachAttributesToVariant(ProductVariant $variant, array $attributesData, Collection $attributesMap): void
    {
        $attrValueIds = [];
        foreach ($attributesData as $item) {
            $slug = $item['attribute_slug'];
            $attribute = $attributesMap->get($slug);
            if (!$attribute) continue; 

            $val = AttributeValue::firstOrCreate(
                ['attribute_id' => $attribute->id, 'value' => $item['value']],
                ['code' => $item['code'] ?? null]
            );
            $attrValueIds[] = $val->id;
        }
        $variant->attributeValues()->sync($attrValueIds);
    }

    protected function deleteImages(array $uuids): void
    {
        $images = ProductImage::whereIn('uuid', $uuids)->get();
        foreach ($images as $img) {
            if ($img->public_id) {
                try {
                    $this->storageService->delete($img->public_id);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("Failed to delete image on cloud: " . $img->public_id);
                }
            }
            $img->delete();
        }
    }

    protected function updateImagesState(array $imagesState): void
    {
        foreach ($imagesState as $state) {
            ProductImage::where('uuid', $state['uuid'])->update([
                'is_primary' => $state['is_primary']
            ]);
        }
    }

    protected function processImages(Product $product, array $files): void
    {
        foreach ($files as $index => $file) {
            $dto = $this->storageService->upload($file, 'products/' . $product->uuid);
            ProductImage::create([
                'product_id' => $product->id,
                'image_url'  => $dto->url,
                'public_id'  => $dto->publicId,
                'is_primary' => $index === 0,
                'sort_order' => $index
            ]);
        }
    }

    protected function updateProductMetadata(Product $product): void
    {
        if ($product->has_variants) {
            $minPrice = $product->variants()->min('price') ?? 0;
            $product->update(['price' => $minPrice]);
        }
    }

    protected function validateUniqueSkuInPayload(array $variants): void
    {
        $skus = array_column($variants, 'sku');
        if (count($skus) !== count(array_unique($skus))) {
            throw new BusinessException(422162, 'Duplicate SKU in variants payload'); 
        }
    }

    public function filter(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $filters['per_page'] = $perPage;
        return $this->repository->filter($filters);
    }

    public function generateAiDescription(array $data): string
    {
        $name = $data['name'];
        $price = isset($data['price']) ? number_format((int)$data['price']) . ' VND' : 'Contact for price'; 
        
        $categoryName = isset($data['category_uuid']) 
            ? Category::where('uuid', $data['category_uuid'])->value('name') 
            : 'Uncategorized'; 
            
        $brandName = isset($data['brand_uuid']) 
            ? Brand::where('uuid', $data['brand_uuid'])->value('name') 
            : 'Generic Brand'; 
        $variantText = "";
        if (!empty($data['variants']) && is_array($data['variants'])) {
            $attrs = [];
            foreach ($data['variants'] as $v) {
                if (!empty($v['attributes'])) {
                    foreach ($v['attributes'] as $attr) {
                        $val = $attr['value'] ?? '';
                        if ($val) $attrs[] = "$val";
                    }
                }
            }
            if (!empty($attrs)) {
                $variantText = "Available options: " . implode(', ', array_unique($attrs));
            }
        }

        $prompt = "You are an expert E-commerce Copywriter. Write a compelling, SEO-friendly product description in ENGLISH based on:\n"
            . "- Product: $name\n"
            . "- Category: $categoryName\n"
            . "- Brand: $brandName\n"
            . "- Price: $price\n"
            . "- $variantText\n\n"
            . "Requirements: ~150 words, professional tone, highlight benefits. Just output content.";

        try {
            $response = Http::timeout(60)->post('http://ollama:11434/api/generate', [
                'model' => 'phi4-mini',
                'prompt' => $prompt,
                'stream' => false,
                'options' => ['temperature' => 0.7]
            ]);

            if ($response->successful()) {
                return $response->json()['response'];
            }
            
            throw new BusinessException(500, 'AI Service failed to respond.');

        } catch (\Exception $e) {
            throw new BusinessException(500, 'Could not connect to AI Server.');
        }
    }
}