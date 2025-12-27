<?php

declare(strict_types=1);

namespace Modules\Order\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Currency\Services\CurrencyService;
use OpenApi\Attributes as OA; 

#[OA\Schema(
    schema: "OrderResource",
    title: "Order Resource",
    properties: [
        new OA\Property(property: "uuid", type: "string", format: "uuid"),
        new OA\Property(property: "code", type: "string"),
        new OA\Property(property: "status", type: "string"),
        new OA\Property(property: "status_label", type: "string"),
        new OA\Property(property: "payment_status", type: "string"),
        new OA\Property(property: "shipping_status", type: "string"),
        new OA\Property(property: "grand_total_formatted", type: "string"),
        new OA\Property(
            property: "shipping_info",
            type: "object",
            properties: [
                new OA\Property(property: "name", type: "string"),
                new OA\Property(property: "phone", type: "string"),
                new OA\Property(property: "full_address", type: "string"),
            ]
        ),
        new OA\Property(property: "items", type: "array", items: new OA\Items(type: "object"))
    ]
)]
class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        $currencyService = app(CurrencyService::class);
        
        return [
            'uuid'            => $this->uuid,
            'code'            => $this->code, 
            'status'          => $this->status,
            'status_label'    => $this->getStatusLabel($this->status), 
            'payment_status'  => $this->payment_status,
            'shipping_status' => $this->shipping_status,
            'notes'           => $this->notes,
            
            'customer' => $this->user ? [
                'uuid'  => $this->user->uuid,
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ] : null,
            
            'shipping_info' => [
                'name'         => $this->shipping_name,
                'phone'        => $this->shipping_phone,
                'full_address' => $this->formatAddressText($this->shipping_address_snapshot),
                'details'      => $this->shipping_address_snapshot
            ],

            'currency_code' => $currencyService->getCurrentCurrency()->code,
            
            'amounts' => [
                'subtotal'         => $currencyService->format((float)$this->subtotal),
                'shipping_fee'     => $currencyService->format((float)$this->shipping_fee),
                'voucher_discount' => $currencyService->format((float)$this->voucher_discount),
                'grand_total'      => $currencyService->format((float)$this->grand_total),
            ],

            'items' => $this->whenLoaded('items', function() use ($currencyService) {
                return $this->items->map(fn($item) => [
                    'uuid'           => $item->uuid,
                    'product_name'   => $item->product_snapshot['name'] ?? 'Unknown',
                    'quantity'       => $item->quantity,
                    'unit_price'     => $currencyService->format((float)$item->unit_price),
                    'total'          => $currencyService->format((float)$item->subtotal),
                    'sku'            => $item->product_snapshot['sku'] ?? null,
                    'image'          => $item->product_snapshot['image'] ?? null,
                    'variant_text'   => $this->formatAttributes($item->product_snapshot['attributes'] ?? [])
                ]);
            }),
            
            'dates' => [
                'created_at' => $this->created_at->format('Y-m-d H:i'),
                'ordered_at' => $this->ordered_at?->format('Y-m-d H:i'),
            ]
        ];
    }

    private function getStatusLabel($status): string
    {
        return match($status->value ?? $status) {
            'pending'    => 'Pending',
            'processing' => 'Processing',
            'shipped'    => 'Shipped', 
            'delivered'  => 'Delivered',
            'cancelled'  => 'Cancelled',
            default      => ucfirst($status->value ?? $status),
        };
    }

    private function formatAttributes(array $attributes): string
    {
        if (empty($attributes)) return '';
        $parts = array_map(fn($attr) => "{$attr['name']}: {$attr['value']}", $attributes);
        return implode(', ', $parts);
    }

    private function formatAddressText($addr): string
    {
        if (!$addr || !is_array($addr)) return 'N/A';
        return implode(', ', array_filter([
            $addr['address_line'] ?? null,
            $addr['ward'] ?? null,
            $addr['district'] ?? null,
            $addr['province'] ?? null
        ]));
    }
}