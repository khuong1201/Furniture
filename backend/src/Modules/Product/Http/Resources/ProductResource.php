<?php

declare(strict_types=1);

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Currency\Services\CurrencyService;
use Modules\Category\Http\Resources\CategoryResource;
use Modules\Brand\Http\Resources\BrandResource;
use OpenApi\Attributes as OA; 

#[OA\Schema(
    schema: "ProductResource",
    title: "Product Resource",
    properties: [
        new OA\Property(property: "uuid", type: "string", format: "uuid"),
        new OA\Property(property: "name", type: "string"),
        new OA\Property(property: "slug", type: "string"),
        new OA\Property(property: "sku", type: "string"),
        new OA\Property(property: "price", type: "number"),
        new OA\Property(property: "stock", type: "array", items: new OA\Items(properties: [
            new OA\Property(property: "warehouse_uuid", type: "string"),
            new OA\Property(property: "quantity", type: "integer"),
        ])),
        new OA\Property(property: "price_formatted", type: "string"),
        new OA\Property(property: "category", ref: "#/components/schemas/CategoryResource"),
        new OA\Property(property: "brand", ref: "#/components/schemas/BrandResource"),
    ]
)]
class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var CurrencyService $currencyService */
        $currencyService = app(CurrencyService::class);
        
        $flashSaleInfo = $this->flash_sale_info; 
        $originalPrice = (int) $this->price;
        $sellingPrice = $flashSaleInfo ? (int) $flashSaleInfo['sale_price'] : $originalPrice;

        $minPrice = $sellingPrice;
        $maxPrice = $sellingPrice;
        $variantMinPrice = null;
        $variantMaxPrice = null;

        $variantsData = $this->whenLoaded('variants', function() use ($currencyService, $flashSaleInfo, &$variantMinPrice, &$variantMaxPrice) {
            return $this->variants->map(function($variant) use ($currencyService, $flashSaleInfo, &$variantMinPrice, &$variantMaxPrice) {
                $stockQty = $variant->relationLoaded('stock') ? $variant->stock->sum('quantity') : 0;
                
                // [UPDATED] Logic lấy stock cho variant
                $stockDetails = $variant->relationLoaded('stock') 
                    ? $variant->stock->map(fn($stk) => [
                        'warehouse_uuid' => $stk->warehouse->uuid ?? null, 
                        'warehouse_name' => $stk->warehouse->name ?? 'Unknown',
                        'quantity' => (int) $stk->quantity
                    ]) 
                    : [];

                $vOriginalPrice = (int)$variant->price;
                $vSellingPrice = $vOriginalPrice;
                
                if ($flashSaleInfo && isset($flashSaleInfo['discount_rate']) && $flashSaleInfo['discount_rate'] > 0) {
                     $discountAmount = $vOriginalPrice * ($flashSaleInfo['discount_rate'] / 100);
                     $vSellingPrice = (int) ($vOriginalPrice - $discountAmount);
                } 

                if (is_null($variantMinPrice) || $vSellingPrice < $variantMinPrice) $variantMinPrice = $vSellingPrice;
                if (is_null($variantMaxPrice) || $vSellingPrice > $variantMaxPrice) $variantMaxPrice = $vSellingPrice;

                return [
                    'uuid' => $variant->uuid,
                    'sku' => $variant->sku,
                    'original_price' => $currencyService->convert($vOriginalPrice),
                    'original_price_formatted' => $currencyService->format($vOriginalPrice),
                    'price' => $currencyService->convert($vSellingPrice),
                    'price_formatted' => $currencyService->format($vSellingPrice),
                    'image' => $variant->image_url,
                    'stock_quantity' => (int)$stockQty,
                    'stock' => $stockDetails, // [FIXED] Thống nhất dùng key 'stock'
                    'attributes' => $variant->relationLoaded('attributeValues') 
                        ? $variant->attributeValues->map(fn($val) => [
                            'attribute_name' => $val->attribute->name ?? 'Unknown', 
                            'value' => $val->value,
                            'code' => $val->code
                        ]) : []
                ];
            });
        });

        if ($this->has_variants && !is_null($variantMinPrice)) {
            $minPrice = $variantMinPrice;
            $maxPrice = $variantMaxPrice;
        }
        
        $priceRange = ($minPrice < $maxPrice) 
            ? $currencyService->format($minPrice) . ' - ' . $currencyService->format($maxPrice)
            : $currencyService->format($minPrice);

        // [UPDATED] Logic lấy stock cho simple product
        $productStockDetails = $this->whenLoaded('stock', function() {
            return $this->stock->map(fn($stk) => [
                'warehouse_uuid' => $stk->warehouse->uuid ?? null,
                'warehouse_name' => $stk->warehouse->name ?? 'Unknown',
                'quantity' => (int) $stk->quantity
            ]);
        });

        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sku' => $this->sku,
            'currency_code' => $currencyService->getCurrentCurrency()->code,
            'price' => $currencyService->convert($minPrice), 
            'price_formatted' => $currencyService->format($minPrice),
            'original_price' => $currencyService->convert($originalPrice),
            'original_price_formatted' => $currencyService->format($originalPrice),
            'price_range' => $priceRange,
            'flash_sale' => $flashSaleInfo,
            'has_variants' => $this->has_variants,
            'is_active' => $this->is_active,
            'sold_count' => (int)$this->sold_count,
            'rating_avg' => (float)$this->rating_avg,
            'rating_count' => (int)$this->rating_count,
            'stock' => $productStockDetails, 
            'category' => new CategoryResource($this->whenLoaded('category')),
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'images' => $this->whenLoaded('images', fn() => $this->images->map(fn($img) => [
                'uuid' => $img->uuid,
                'url' => $img->image_url,
                'is_primary' => $img->is_primary
            ])),
            'variants' => $variantsData,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}