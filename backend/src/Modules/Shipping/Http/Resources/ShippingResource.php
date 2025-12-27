<?php

namespace Modules\Shipping\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "ShippingResource",
    title: "Shipping Resource",
    description: "Định dạng dữ liệu trả về của vận đơn",
    properties: [
        new OA\Property(property: "uuid", type: "string", format: "uuid"),
        new OA\Property(property: "tracking_number", type: "string"),
        new OA\Property(property: "provider", type: "string"),
        new OA\Property(property: "status", type: "string", enum: ["pending", "shipped", "delivered"]),
        new OA\Property(property: "fee", type: "number"),
        new OA\Property(property: "fee_formatted", type: "string", example: "30.000 VND"),
        new OA\Property(
            property: "consignee",
            type: "object",
            properties: [
                new OA\Property(property: "name", type: "string"),
                new OA\Property(property: "phone", type: "string"),
                new OA\Property(property: "address", type: "string"),
            ]
        ),
        new OA\Property(
            property: "dates",
            type: "object",
            properties: [
                new OA\Property(property: "created_at", type: "string", format: "date-time"),
                new OA\Property(property: "shipped_at", type: "string", format: "date-time", nullable: true),
                new OA\Property(property: "delivered_at", type: "string", format: "date-time", nullable: true),
            ]
        ),
        new OA\Property(property: "order", type: "object", nullable: true)
    ]
)]
class ShippingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid'            => $this->uuid,
            'tracking_number' => $this->tracking_number,
            'provider'        => $this->provider,
            'status'          => $this->status,
            'fee'             => (float) $this->fee,
            'fee_formatted'   => number_format((float) $this->fee, 0, ',', '.') . ' VND',

            'consignee' => [
                'name'    => $this->consignee_name,
                'phone'   => $this->consignee_phone,
                'address' => $this->address_full,
            ],

            'dates' => [
                'created_at'   => $this->created_at?->format('Y-m-d H:i'),
                'shipped_at'   => $this->shipped_at?->format('Y-m-d H:i'),
                'delivered_at' => $this->delivered_at?->format('Y-m-d H:i'),
            ],

            'order' => $this->whenLoaded('order', function () {
                return [
                    'uuid' => $this->order->uuid,
                    'code' => $this->order->code,
                    'status' => $this->order->status,
                ];
            }),
        ];
    }
}