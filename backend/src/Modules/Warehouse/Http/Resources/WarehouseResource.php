<?php

declare(strict_types=1);

namespace Modules\Warehouse\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "WarehouseResource",
    properties: [
        new OA\Property(property: "uuid", type: "string", format: "uuid"),
        new OA\Property(property: "name", type: "string", example: "Kho Hồ Chí Minh"),
        new OA\Property(property: "location", type: "string"),
        new OA\Property(property: "is_active", type: "boolean"),
    ]
)]
class WarehouseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'      => $this->uuid,
            'name'      => $this->name,
            'location'  => $this->location,
            'is_active' => (bool) $this->is_active,
        ];
    }
}