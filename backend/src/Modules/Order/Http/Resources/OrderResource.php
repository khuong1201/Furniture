<?php

declare(strict_types=1);

namespace Modules\Order\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Currency\Services\CurrencyService;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        $currencyService = app(CurrencyService::class);
        
        return [
            'uuid' => $this->uuid,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'shipping_status' => $this->shipping_status,
            'notes' => $this->notes,
            'shipping_address' => $this->shipping_address_snapshot,
            
            'currency_code' => $currencyService->getCurrentCurrency()->code,
            
            'total_amount' => $currencyService->convert((float)$this->total_amount),
            'total_formatted' => $currencyService->format((float)$this->total_amount),
            'voucher_discount' => $currencyService->convert((float)$this->voucher_discount),
            
            'items' => $this->whenLoaded('items', function() use ($currencyService) {
                return $this->items->map(fn($item) => [
                    'product_name' => $item->product_snapshot['name'] ?? 'Unknown',
                    'quantity' => $item->quantity,
                    'unit_price_formatted' => $currencyService->format((float)$item->unit_price),
                    'subtotal_formatted' => $currencyService->format((float)$item->subtotal),
                    'sku' => $item->product_snapshot['sku'] ?? null,
                    
                    // Fallback ảnh: Nếu snapshot không có, thử lấy từ relation variant hiện tại (nếu sản phẩm chưa bị xóa)
                    'image' => $item->product_snapshot['image'] 
                        ?? $item->variant?->image_url 
                        ?? $item->variant?->product?->images->first()?->image_url 
                        ?? null,
                        
                    'attributes' => $item->product_snapshot['attributes'] ?? []
                ]);
            }),
            
            'ordered_at' => $this->ordered_at,
        ];
    }
}