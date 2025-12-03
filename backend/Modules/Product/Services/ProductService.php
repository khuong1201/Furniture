<?php

namespace Modules\Product\Services;

use Modules\Shared\Services\BaseService;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use Modules\Product\Domain\Models\Product;
use Modules\Product\Domain\Models\ProductVariant;
use Modules\Product\Domain\Models\AttributeValue;
use Modules\Warehouse\Domain\Models\Warehouse;
use Modules\Category\Domain\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Modules\Inventory\Domain\Models\InventoryStock;

class ProductService extends BaseService
{
    public function __construct(
        ProductRepositoryInterface $repo,
        protected ProductImageService $imageService
    ) {
        parent::__construct($repo);
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
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

            if ($product->has_variants && !empty($data['variants'])) {

                foreach ($data['variants'] as $variantData) {
                    $this->createVariant($product, $variantData);
                }
            } else {
                $this->createVariant($product, [
                    'sku' => $data['sku'],
                    'price' => $data['price'],
                    'weight' => $data['weight'] ?? 0,
                    'stock' => $data['warehouse_stock'] ?? [], 
                    'attributes' => [] 
                ]);
            }

            if (!empty($data['images']) && is_array($data['images'])) {
                $this->imageService->uploadMultiple($product, $data['images']);
            }

            return $product->load(['variants.attributeValues', 'images', 'category']);
        });
    }

    public function update(string $uuid, array $data): Model
    {
        $product = $this->findByUuidOrFail($uuid);

        return DB::transaction(function () use ($product, $data) {
  
            $updateData = [];
            if (isset($data['name'])) $updateData['name'] = $data['name'];
            if (isset($data['description'])) $updateData['description'] = $data['description'];
            if (isset($data['is_active'])) $updateData['is_active'] = $data['is_active'];
            
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
            elseif (!$product->has_variants && isset($data['warehouse_stock'])) {
             
                $defaultVariant = $product->variants()->first();
                if ($defaultVariant) {
                    $this->syncStock($defaultVariant, $data['warehouse_stock']);
                }
            }

            if (!empty($data['images']) && is_array($data['images'])) {
                $this->imageService->uploadMultiple($product, $data['images']);
            }

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

        if (!empty($data['attributes'])) {
            $attrValueIds = AttributeValue::whereIn('uuid', $data['attributes'])->pluck('id')->toArray();
            $variant->attributeValues()->sync($attrValueIds);
        }

        if (!empty($data['stock'])) {
            $this->syncStock($variant, $data['stock']);
        }
    }

    protected function syncVariants(Product $product, array $variantsData): void
    {
        $keptVariantIds = [];

        foreach ($variantsData as $vData) {

            if (isset($vData['uuid'])) {
                $variant = ProductVariant::where('uuid', $vData['uuid'])
                    ->where('product_id', $product->id)
                    ->first();

                if ($variant) {
                    $variant->update([
                        'sku' => $vData['sku'],
                        'price' => $vData['price'],
                        'weight' => $vData['weight'] ?? $variant->weight
                    ]);
                    
                    if (isset($vData['stock'])) {
                        $this->syncStock($variant, $vData['stock']);
                    }
                    
                    $keptVariantIds[] = $variant->id;
                }
            } 
            else {
                $newVariant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $vData['sku'],
                    'price' => $vData['price'],
                    'weight' => $vData['weight'] ?? 0,
                ]);

                if (!empty($vData['attributes'])) {
                    $attrValueIds = AttributeValue::whereIn('uuid', $vData['attributes'])->pluck('id')->toArray();
                    $newVariant->attributeValues()->sync($attrValueIds);
                }

                if (!empty($vData['stock'])) {
                    $this->syncStock($newVariant, $vData['stock']);
                }

                $keptVariantIds[] = $newVariant->id;
            }
        }

        $product->variants()->whereNotIn('id', $keptVariantIds)->delete();
    }

    protected function syncStock(ProductVariant $variant, array $stockData): void
    {
        $variant->stock()->delete(); 

        foreach ($stockData as $stock) {
            $whId = Warehouse::where('uuid', $stock['warehouse_uuid'])->value('id');
            
            if ($whId) {
                InventoryStock::create([
                    'warehouse_id' => $whId,
                    'product_variant_id' => $variant->id,
                    'quantity' => $stock['quantity']
                ]);
            }
        }
    }
}