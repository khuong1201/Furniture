<?php

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
            'sku' => $this->sku, // SKU chung (nếu có)
            'price' => $this->price, // Giá chung (nếu có)
            'has_variants' => $this->has_variants,
            'is_active' => $this->is_active,
            
            // Trả về danh mục
            'category' => $this->whenLoaded('category', function() {
                return [
                    'uuid' => $this->category->uuid,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                ];
            }),

            // Trả về danh sách ảnh
            'images' => $this->whenLoaded('images', function() {
                return $this->images->map(fn($img) => [
                    'uuid' => $img->uuid,
                    'url' => $img->image_url,
                    'is_primary' => $img->is_primary
                ]);
            }),

            // --- PHẦN QUAN TRỌNG: VARIANTS & ATTRIBUTES ---
            'variants' => $this->whenLoaded('variants', function() {
                return $this->variants->map(function($variant) {
                    return [
                        'uuid' => $variant->uuid,
                        'sku' => $variant->sku,
                        'price' => $variant->price,
                        'weight' => $variant->weight,
                        'image' => $variant->image_url,
                        'stock_quantity' => $variant->stock->sum('quantity'), // Tổng tồn kho
                        
                        // Danh sách thuộc tính của variant này (VD: Màu: Đỏ, Size: M)
                        'attributes' => $variant->attributeValues->map(fn($val) => [
                            'attribute_name' => $val->attribute->name, // VD: Màu sắc
                            'attribute_slug' => $val->attribute->slug, // VD: color
                            'value' => $val->value, // VD: Đỏ
                            'code' => $val->code,   // VD: #FF0000
                            'value_uuid' => $val->uuid
                        ])
                    ];
                });
            }),
            
            // Bổ sung: Danh sách tất cả thuộc tính có sẵn của sản phẩm này (Để Frontend vẽ bộ lọc)
            // Ví dụ: Sản phẩm này có những màu nào? Những size nào?
            'available_options' => $this->getAvailableOptions(), 

            'created_at' => $this->created_at,
        ];
    }

    /**
     * Helper để gom nhóm thuộc tính
     * VD: Color -> [Red, Blue], Size -> [S, M]
     */
    protected function getAvailableOptions()
    {
        if (!$this->relationLoaded('variants')) return [];

        $options = [];
        foreach ($this->variants as $variant) {
            foreach ($variant->attributeValues as $val) {
                $attrName = $val->attribute->name;
                $attrId = $val->attribute->uuid;
                
                if (!isset($options[$attrId])) {
                    $options[$attrId] = [
                        'attribute_uuid' => $attrId,
                        'attribute_name' => $attrName,
                        'values' => []
                    ];
                }

                // Thêm giá trị nếu chưa có (tránh trùng lặp)
                $exists = collect($options[$attrId]['values'])->contains('uuid', $val->uuid);
                if (!$exists) {
                    $options[$attrId]['values'][] = [
                        'uuid' => $val->uuid,
                        'value' => $val->value,
                        'code' => $val->code
                    ];
                }
            }
        }
        
        return array_values($options);
    }
}