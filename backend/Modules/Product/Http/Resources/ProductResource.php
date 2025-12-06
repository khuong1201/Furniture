<?php

declare(strict_types=1);

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Currency\Services\CurrencyService;
use Modules\Category\Http\Resources\CategoryResource;
use Carbon\Carbon;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var CurrencyService $currencyService */
        $currencyService = app(CurrencyService::class);
        
        // 1. Lấy thông tin Flash Sale (Đã xử lý ở Model/Repository)
        $flashSaleInfo = $this->flash_sale_info; 
        
        $originalPrice = (int) $this->price;
        $sellingPrice = $flashSaleInfo ? (int) $flashSaleInfo['sale_price'] : $originalPrice;

        // 2. [UPDATE] Logic Range Giá phải hiển thị giá SAU GIẢM (Selling Price)
        // Nếu đang sale, khách muốn nhìn thấy range giá sale (Ví dụ: 80k - 120k) chứ không phải giá gốc
        $minPrice = $sellingPrice;
        $maxPrice = $sellingPrice;
        
        // Chuẩn bị biến để tính range cho variant sau này
        $variantMinPrice = null;
        $variantMaxPrice = null;

        // 3. Logic Đếm ngược (Giữ nguyên logic của bạn, chỉ optimize gọn lại)
        $endsAt = null;
        $secondsRemaining = 0;
        $endsIn = null;

        if ($flashSaleInfo && isset($flashSaleInfo['end_date'])) {
            $endDate = $flashSaleInfo['end_date'];
            $now = now();
            $endsAt = $endDate->toIso8601String();
            $secondsRemaining = max(0, $endDate->diffInSeconds($now)); 
            $endsIn = $endDate->diffInHours($now) > 24 
                ? $endDate->diffInDays($now) . ' days left' 
                : $endDate->diff($now)->format('%H:%I:%S');
        }

        // 4. Transform Variants (FIX LOGIC GIÁ TẠI ĐÂY)
        $variantsData = $this->whenLoaded('variants', function() use ($currencyService, $flashSaleInfo, &$variantMinPrice, &$variantMaxPrice) {
            return $this->variants->map(function($variant) use ($currencyService, $flashSaleInfo, &$variantMinPrice, &$variantMaxPrice) {
                $stockQty = $variant->relationLoaded('stock') ? $variant->stock->sum('quantity') : 0;
                
                // Giá gốc của variant
                $vOriginalPrice = (int)$variant->price;
                
                // --- [LOGIC MỚI] Tính giá bán của Variant dựa trên Flash Sale ---
                $vSellingPrice = $vOriginalPrice;
                
                if ($flashSaleInfo) {
                    // Cách 1: Nếu Flash Sale giảm theo % (Ưu tiên cách này cho biến thể)
                    if (isset($flashSaleInfo['discount_rate']) && $flashSaleInfo['discount_rate'] > 0) {
                         $discountAmount = $vOriginalPrice * ($flashSaleInfo['discount_rate'] / 100);
                         $vSellingPrice = $vOriginalPrice - $discountAmount;
                    } 
                    // Cách 2: Nếu Flash Sale giảm tiền cố định (Fixed amount)
                    // Lưu ý: Giảm cố định thường áp dụng cho SP cha, nếu áp cho variant giá thấp coi chừng bị âm tiền.
                    // Tạm thời ở đây tôi dùng logic kế thừa % giảm giá là an toàn nhất.
                }

                $vSellingPrice = (int) max(0, $vSellingPrice);

                // Cập nhật min/max range
                if (is_null($variantMinPrice) || $vSellingPrice < $variantMinPrice) $variantMinPrice = $vSellingPrice;
                if (is_null($variantMaxPrice) || $vSellingPrice > $variantMaxPrice) $variantMaxPrice = $vSellingPrice;

                return [
                    'uuid' => $variant->uuid,
                    'sku' => $variant->sku,
                    
                    // Frontend cần cả 2 giá để gạch ngang giá cũ
                    'original_price' => $currencyService->convert($vOriginalPrice),
                    'original_price_formatted' => $currencyService->format($vOriginalPrice),
                    
                    'price' => $currencyService->convert($vSellingPrice), // Giá bán thực tế
                    'price_formatted' => $currencyService->format($vSellingPrice),
                    
                    'image' => $variant->image_url,
                    'stock_quantity' => (int)$stockQty,
                    'attributes' => $variant->relationLoaded('attributeValues') 
                        ? $variant->attributeValues->map(fn($val) => [
                            'attribute_name' => $val->attribute->name ?? 'Unknown', 
                            'value' => $val->value,
                            'code' => $val->code
                        ]) : []
                ];
            });
        });

        // 5. Cập nhật lại Price Range hiển thị ra ngoài dựa trên Variant (nếu có)
        if ($this->has_variants && !is_null($variantMinPrice)) {
            $minPrice = $variantMinPrice;
            $maxPrice = $variantMaxPrice;
        }
        
        $priceRange = ($minPrice < $maxPrice) 
            ? $currencyService->format($minPrice) . ' - ' . $currencyService->format($maxPrice)
            : $currencyService->format($minPrice); // Nếu min=max thì hiện 1 giá thôi

        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            
            // --- PRICING (Đã fix logic hiển thị đúng giá bán) ---
            'currency_code' => $currencyService->getCurrentCurrency()->code,
            
            // Giá hiển thị đại diện (nếu có biến thể thì lấy giá thấp nhất)
            'price' => $currencyService->convert($minPrice), 
            'price_formatted' => $currencyService->format($minPrice),
            
            // Giá gốc đại diện (để gạch ngang ở ngoài danh sách)
            'original_price' => $currencyService->convert($originalPrice),
            'original_price_formatted' => $currencyService->format($originalPrice),
            
            'price_range' => $priceRange,
            
            // --- FLASH SALE OBJECT ---
            'flash_sale' => $flashSaleInfo ? [
                'is_active'        => true,
                'campaign_name'    => $flashSaleInfo['campaign_name'],
                'discount_percent' => $flashSaleInfo['discount_rate'], // Frontend dùng số này để hiện tag -20%
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
            
            'variants' => $variantsData,
            
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}