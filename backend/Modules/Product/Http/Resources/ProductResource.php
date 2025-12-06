<?php

declare(strict_types=1);

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Currency\Services\CurrencyService;
use Modules\Product\Http\Resources\CategoryResource; // Giả sử namespace này

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var CurrencyService $currencyService */
        $currencyService = app(CurrencyService::class);
        
        // 1. Lấy thông tin Flash Sale (Được tính toán tự động từ Trait HasPromotions)
        // Vì Repository đã eager load 'promotions' => active(), logic này không tốn query DB.
        $flashSaleInfo = $this->flash_sale_info; 

        // 2. Xác định giá cơ bản (Base Price - BigInt)
        $originalPrice = (int) $this->price;
        
        // 3. Xác định giá bán thực tế (Selling Price)
        // Nếu có Flash Sale -> dùng sale_price, ngược lại dùng giá gốc
        $sellingPrice = $flashSaleInfo ? (int) $flashSaleInfo['sale_price'] : $originalPrice;

        // 4. Xử lý Variants (Logic giá Min-Max cho Price Range)
        $minPrice = $sellingPrice;
        $maxPrice = $sellingPrice;
        
        if ($this->has_variants && $this->relationLoaded('variants')) {
            $variants = $this->variants;
            if ($variants->isNotEmpty()) {
                // Lưu ý: Logic này lấy range giá của variants hiện tại
                // Nếu muốn chính xác tuyệt đối với Flash Sale trên từng variant, cần logic phức tạp hơn.
                // Ở đây ta lấy min/max của variants để hiển thị khoảng giá.
                $minPrice = (int) $variants->min('price');
                $maxPrice = (int) $variants->max('price');
            }
        }

        // Format Range (Chỉ hiển thị nếu min != max)
        $priceRange = ($minPrice < $maxPrice) 
            ? $currencyService->format($minPrice) . ' - ' . $currencyService->format($maxPrice)
            : null;

        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            
            // --- CURRENCY & PRICING LOGIC ---
            'currency_code' => $currencyService->getCurrentCurrency()->code,
            
            // 1. GIÁ BÁN (Giá khách phải trả - Số to, màu đỏ)
            'price' => $currencyService->convert($sellingPrice), 
            'price_formatted' => $currencyService->format($sellingPrice),
            
            // 2. GIÁ GỐC (Giá niêm yết - Số nhỏ, gạch ngang)
            // FIX: Luôn trả về giá trị để Frontend so sánh. Nếu price < original_price thì gạch ngang.
            'original_price' => $currencyService->convert($originalPrice),
            'original_price_formatted' => $currencyService->format($originalPrice),

            // 3. KHOẢNG GIÁ (Cho sản phẩm có biến thể)
            'price_range' => $priceRange,
            
            // 4. INFO FLASH SALE (Tem giảm giá, đếm ngược...)
            'flash_sale' => $flashSaleInfo ? [
                'is_active'        => true,
                'campaign_name'    => $flashSaleInfo['campaign_name'], // Đã sửa key khớp với Trait
                'discount_percent' => $flashSaleInfo['discount_rate'],
                'saved_amount'     => $currencyService->format($flashSaleInfo['discount_amount']),
                'ends_in'          => null 
            ] : null,
            // --------------------------------

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
                    $vPrice = (int)$variant->price;

                    return [
                        'uuid' => $variant->uuid,
                        'sku' => $variant->sku,
                        'price' => $currencyService->convert($vPrice),
                        'price_formatted' => $currencyService->format($vPrice),
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
            
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}