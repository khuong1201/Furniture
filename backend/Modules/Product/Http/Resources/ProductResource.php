<?php

declare(strict_types=1);

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            'price' => (float)$this->price,
            'has_variants' => $this->has_variants,
            'is_active' => $this->is_active,

            // Stats
            'sold_count' => $this->sold_count,
            'rating_avg' => (float)$this->rating_avg,
            'rating_count' => $this->rating_count,

            // Relations
            'category' => $this->whenLoaded('category', function () {
                return [
                    'uuid' => $this->category->uuid,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                ];
            }),

            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(fn($img) => [
                    'uuid' => $img->uuid,
                    'url' => $img->image_url,
                    'is_primary' => $img->is_primary
                ]);
            }),

            'promotions' => $this->whenLoaded('promotions', function () {
                return $this->promotions->map(fn($promo) => [
                    'name' => $promo->name,
                    'type' => $promo->type,
                    'value' => (float)$promo->value,
                ]);
            }),

            // Variants & Stock
            'variants' => $this->whenLoaded('variants', function () {
                return $this->variants->map(function ($variant) {
                    // Safe access stock quantity
                    $stockQty = $variant->relationLoaded('stock')
                        ? $variant->stock->sum('quantity')
                        : 0;

                    return [
                        'uuid' => $variant->uuid,
                        'sku' => $variant->sku,
                        'price' => (float)$variant->price,
                        'weight' => $variant->weight,
                        'image' => $variant->image_url,
                        'stock_quantity' => (int)$stockQty,

                        // --- PHẦN SỬA ĐỔI ---
                        // Kiểm tra trực tiếp trên biến $variant, KHÔNG dùng $this->whenLoaded
                        'attributes' => $variant->relationLoaded('attributeValues')
                            ? $variant->attributeValues->map(fn($val) => [
                                'attribute_name' => $val->attribute->name ?? 'Unknown',
                                'attribute_slug' => $val->attribute->slug ?? 'unknown',
                                'value' => $val->value,
                                'code' => $val->code,
                                'value_uuid' => $val->uuid
                            ])
                            : [],
                        // --------------------
                    ];
                });
            }),

            'created_at' => $this->created_at,
        ];
    }
}