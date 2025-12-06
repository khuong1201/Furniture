<?php

declare(strict_types=1);

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Currency\Services\CurrencyService;
use Modules\Category\Http\Resources\CategoryResource;
use Carbon\Carbon; // [FIX] Import Carbon để xử lý ngày giờ

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var CurrencyService $currencyService */
        $currencyService = app(CurrencyService::class);
        
        // 1. Lấy thông tin Flash Sale
        $flashSaleInfo = $this->flash_sale_info; 
        
        $originalPrice = (int) $this->price;
        $sellingPrice = $flashSaleInfo ? (int) $flashSaleInfo['sale_price'] : $originalPrice;

        // 2. Logic Range Giá
        $minPrice = $sellingPrice;
        $maxPrice = $sellingPrice;
        
        if ($this->has_variants && $this->relationLoaded('variants')) {
            $variants = $this->variants;
            if ($variants->isNotEmpty()) {
                $minPrice = (int) $variants->min('price');
                $maxPrice = (int) $variants->max('price');
            }
        }

        $priceRange = ($minPrice < $maxPrice) 
            ? $currencyService->format($minPrice) . ' - ' . $currencyService->format($maxPrice)
            : null;

        // 3. [FIX] LOGIC TÍNH THỜI GIAN ĐẾM NGƯỢC (ENDS IN)
        $endsIn = null;
        $endsAt = null;
        $secondsRemaining = 0;

        if ($flashSaleInfo && isset($flashSaleInfo['end_date'])) {
            $endDate = $flashSaleInfo['end_date']; // Carbon object từ Trait
            $now = now();
            
            // a. Timestamp chuẩn ISO (Cho Frontend/JS parse)
            $endsAt = $endDate->toIso8601String();
            
            // b. Số giây còn lại (Cho đồng hồ đếm ngược, dùng max 0 để không bị số âm)
            $secondsRemaining = max(0, $endDate->diffInSeconds($now)); 

            // c. Text hiển thị nhanh (VD: "2 days left" hoặc "05:30:00")
            if ($endDate->diffInHours($now) > 24) {
                $endsIn = $endDate->diffInDays($now) . ' days left';
            } else {
                $endsIn = $endDate->diff($now)->format('%H:%I:%S');
            }
        }

        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            
            // --- PRICING ---
            'currency_code' => $currencyService->getCurrentCurrency()->code,
            'price' => $currencyService->convert($sellingPrice), 
            'price_formatted' => $currencyService->format($sellingPrice),
            'original_price' => $currencyService->convert($originalPrice),
            'original_price_formatted' => $currencyService->format($originalPrice),
            'price_range' => $priceRange,
            
            // --- FLASH SALE OBJECT ---
            'flash_sale' => $flashSaleInfo ? [
                'is_active'        => true,
                'campaign_name'    => $flashSaleInfo['campaign_name'],
                'discount_percent' => $flashSaleInfo['discount_rate'],
                'saved_amount'     => $currencyService->format($flashSaleInfo['discount_amount']),
                
                // [FIX] Trả về đầy đủ thông tin thời gian
                'ends_at'          => $endsAt, 
                'seconds_remaining'=> $secondsRemaining,
                'ends_in'          => $endsIn 
            ] : null,

            'has_variants' => $this->has_variants,
            'is_active' => $this->is_active,
            'sold_count' => (int)$this->sold_count,
            'rating_avg' => (float)$this->rating_avg,
            'rating_count' => (int)$this->rating_count,
            
            'category' => new CategoryResource($this->whenLoaded('category')),

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

                        'attributes' => $variant->relationLoaded('attributeValues') 
                            ? $variant->attributeValues->map(fn($val) => [
                                'attribute_name' => $val->attribute->name ?? 'Unknown', 
                                'value' => $val->value,
                                'code' => $val->code
                            ])
                            : []
                    ];
                });
            }),
            
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}