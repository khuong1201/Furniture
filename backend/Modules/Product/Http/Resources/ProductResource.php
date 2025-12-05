<?php

declare(strict_types=1);

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Currency\Services\CurrencyService;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        $currencyService = app(CurrencyService::class);
        
        // Lấy giá gốc từ DB (Integer - Base Unit)
        $basePrice = (int) $this->price; 
        $variants = $this->whenLoaded('variants');
        
        $minPrice = $basePrice;
        $maxPrice = $basePrice;

        if ($this->has_variants && $variants instanceof \Illuminate\Support\Collection) {
            $minPrice = $variants->min('price') ?? $basePrice;
            $maxPrice = $variants->max('price') ?? $basePrice;
        }

        // Format Range
        $priceRange = ($minPrice < $maxPrice) 
            ? $currencyService->format((int)$minPrice) . ' - ' . $currencyService->format((int)$maxPrice)
            : null;

        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku, 
            
            // --- CURRENCY LOGIC ---
            'currency_code' => $currencyService->getCurrentCurrency()->code,
            
            // 1. Giá trị số (đã quy đổi ra float theo currency hiện tại)
            'price' => $currencyService->convert((int)$minPrice), 
            
            // 2. Giá trị hiển thị (String có symbol)
            'price_formatted' => $currencyService->format((int)$minPrice),
            
            // 3. Range giá
            'price_range' => $priceRange,
            // ----------------------

            'has_variants' => $this->has_variants,
            'is_active' => $this->is_active,
            
            'sold_count' => (int)$this->sold_count,
            'rating_avg' => (float)$this->rating_avg,
            'rating_count' => (int)$this->rating_count,
            
            'category' => $this->whenLoaded('category', function() {
                return [
                    'uuid' => $this->category->uuid,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                ];
            }),

            'images' => $this->whenLoaded('images', function() {
                return $this->images->map(fn($img) => [
                    'uuid' => $img->uuid,
                    'url' => $img->image_url,
                    'is_primary' => $img->is_primary
                ]);
            }),
            
            'variants' => $this->whenLoaded('variants', function() use ($currencyService) {
                return $this->variants->map(function($variant) use ($currencyService) {
                    $stockQty = $variant->relationLoaded('stock') ? $variant->stock->sum('quantity') : 0;
                    $vPrice = (int)$variant->price; // Ép kiểu int

                    return [
                        'uuid' => $variant->uuid,
                        'sku' => $variant->sku,
                        
                        'price' => $currencyService->convert($vPrice),
                        'price_formatted' => $currencyService->format($vPrice),
                        
                        'weight' => $variant->weight,
                        'image' => $variant->image_url,
                        'stock_quantity' => (int)$stockQty,
                        
                        'attributes' => $this->whenLoaded('attributeValues', function() use ($variant) {
                            if (!$variant->relationLoaded('attributeValues')) return [];
                            return $variant->attributeValues->map(fn($val) => [
                                'attribute_name' => $val->attribute->name ?? 'Unknown',
                                'value' => $val->value,
                                'code' => $val->code
                            ]);
                        }, [])
                    ];
                });
            }),
            
            'created_at' => $this->created_at,
        ];
    }
}