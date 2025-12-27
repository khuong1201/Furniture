<?php

declare(strict_types=1);

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "ProductVariantResource",
    properties: [
        new OA\Property(property: "uuid", type: "string"),
        new OA\Property(property: "sku", type: "string"),
        new OA\Property(property: "name", type: "string", example: "Áo Polo (Đỏ, XL)"),
        new OA\Property(property: "price", type: "integer"),
        new OA\Property(property: "image", type: "string", nullable: true),
        new OA\Property(property: "product_name", type: "string"),
    ]
)]
class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'  => $this->uuid,
            'sku'   => $this->sku,
            'name'  => $this->name, // Trả về tên biến thể
            'price' => (int) $this->price,
            'image' => $this->image_url,
            'product_name' => $this->product->name ?? null, 
        ];
    }
}