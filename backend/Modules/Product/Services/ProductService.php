<?php

declare(strict_types=1);

namespace Modules\Product\Services;

use Modules\Shared\Services\BaseService;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use Modules\Product\Domain\Models\Product;
use Modules\Product\Domain\Models\ProductVariant;
use Modules\Product\Domain\Models\AttributeValue;
use Modules\Product\Domain\Models\Attribute;
use Modules\Product\Domain\Models\ProductImage;
use Modules\Category\Domain\Models\Category;
// Interfaces để giao tiếp Module khác
use Modules\Shared\Contracts\MediaServiceInterface;
use Modules\Shared\Contracts\InventoryServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ProductService extends BaseService
{
    public function __construct(
        ProductRepositoryInterface $repo,
        protected MediaServiceInterface $mediaService,
        protected InventoryServiceInterface $inventoryService
    ) {
        parent::__construct($repo);
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // 1. Create Product Core
            $productData = [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'has_variants' => $data['has_variants'] ?? false,
            ];

            if (isset($data['category_uuid'])) {
                $productData['category_id'] = Category::where('uuid', $data['category_uuid'])->value('id');
            }

            if (!$productData['has_variants']) {
                $productData['price'] = $data['price'];
                $productData['sku'] = $data['sku'];
            }

            $product = parent::create($productData);

            // 2. Handle Variants & Inventory
            if ($product->has_variants && !empty($data['variants'])) {
                foreach ($data['variants'] as $variantData) {
                    $this->createVariant($product, $variantData);
                }
            } else {
                // Tạo 1 variant ẩn cho sản phẩm đơn để dùng chung logic kho
                $this->createVariant($product, [
                    'sku' => $data['sku'],
                    'price' => $data['price'],
                    'weight' => $data['weight'] ?? 0,
                    'stock' => $data['warehouse_stock'] ?? [], 
                    'attributes' => [] 
                ]);
            }

            // 3. Handle Images (Gọi Media Module)
            if (!empty($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $index => $file) {
                    // Upload vật lý
                    $uploadData = $this->mediaService->upload($file, 'products');
                    
                    // Lưu metadata vào Product Module
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_url' => $uploadData['url'],
                        'public_id' => $uploadData['public_id'] ?? null,
                        'is_primary' => $index === 0,
                        'sort_order' => $index
                    ]);
                }
            }

            // Load relationships để trả về
            return $product->load(['variants.attributeValues', 'images', 'category']);
        });
    }

    public function update(string $uuid, array $data): Model
    {
        $product = $this->findByUuidOrFail($uuid);

        return DB::transaction(function () use ($product, $data) {
            $updateData = collect($data)->only(['name', 'description', 'is_active'])->toArray();
            
            if (isset($data['category_uuid'])) {
                $updateData['category_id'] = Category::where('uuid', $data['category_uuid'])->value('id');
            }

            if (!$product->has_variants) {
                if (isset($data['price'])) $updateData['price'] = $data['price'];
                if (isset($data['sku'])) $updateData['sku'] = $data['sku'];
            }

            $product->update($updateData);

            if ($product->has_variants && isset($data['variants'])) {
                $this->syncVariants($product, $data['variants']);
            } 
            // Note: Logic update ảnh phức tạp nên tách API riêng (StoreProductImageRequest)
            
            return $product->load(['variants', 'images']);
        });
    }

    protected function createVariant(Product $product, array $data): void
    {
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $data['sku'],
            'price' => $data['price'],
            'weight' => $data['weight'] ?? 0,
        ]);

        // Attributes Logic
        if (!empty($data['attributes'])) {
            $attrValueIds = [];
            foreach ($data['attributes'] as $attrItem) {
                $attribute = Attribute::where('slug', $attrItem['attribute_slug'])->first();
                if (!$attribute) continue;

                if (!empty($attrItem['is_new'])) {
                    $val = AttributeValue::firstOrCreate(
                        ['attribute_id' => $attribute->id, 'value' => $attrItem['value']],
                        ['code' => $attrItem['code'] ?? null]
                    );
                    $attrValueIds[] = $val->id;
                } else {
                    $val = AttributeValue::where('uuid', $attrItem['value'])->first();
                    if ($val) $attrValueIds[] = $val->id;
                }
            }
            $variant->attributeValues()->sync($attrValueIds);
        }

        // Inventory Logic: Gọi sang Module Inventory qua Interface
        if (!empty($data['stock'])) {
            $this->inventoryService->syncStock($variant->id, $data['stock']);
        }
    }

    protected function syncVariants(Product $product, array $variantsData): void
    {
        foreach ($variantsData as $vData) {
            if (isset($vData['uuid'])) {
                // Update existing variant
                $variant = ProductVariant::where('uuid', $vData['uuid'])
                    ->where('product_id', $product->id)
                    ->first();

                if ($variant) {
                    $variant->update([
                        'sku' => $vData['sku'], 
                        'price' => $vData['price']
                    ]);
                    
                    // Sync stock update
                    if (isset($vData['stock'])) {
                        $this->inventoryService->syncStock($variant->id, $vData['stock']);
                    }
                }
            } else {
                // Create new variant
                $this->createVariant($product, $vData);
            }
        }
    }
}