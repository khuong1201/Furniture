<?php

declare(strict_types=1);

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Inventory\Enums\InventoryStatus;
use Modules\Product\Http\Resources\ProductVariantResource;
use Modules\Warehouse\Http\Resources\WarehouseResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "InventoryResource",
    properties: [
        new OA\Property(property: "uuid", type: "string", format: "uuid"),
        new OA\Property(property: "quantity", type: "integer"),
        new OA\Property(property: "min_threshold", type: "integer"),
        new OA\Property(property: "status", type: "string", enum: ["out_of_stock", "low_stock", "in_stock"], example: "in_stock"),
        new OA\Property(property: "status_label", type: "string", example: "In Stock"),
        new OA\Property(property: "status_color", type: "string", example: "success"),
        new OA\Property(property: "last_updated", type: "string", format: "date-time"),
    ]
)]
class InventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Logic to determine Status using Enum
        $statusEnum = InventoryStatus::IN_STOCK;
        
        // Default threshold is 10 if null
        if ($this->quantity == 0) {
            $statusEnum = InventoryStatus::OUT_OF_STOCK;
        } elseif ($this->quantity <= ($this->min_threshold ?? 10)) {
            $statusEnum = InventoryStatus::LOW_STOCK;
        }elseif (
            $this->quantity > 0 &&
            $this->updated_at &&
            $this->updated_at->lte(now()->subDays(90))
        ) {
            $statusEnum = InventoryStatus::OLD_STOCK;
        }

        return [
            'uuid'          => $this->uuid,
            'quantity'      => (int) $this->quantity,
            'min_threshold' => (int) $this->min_threshold,
            
            // Return Enum value ('out_of_stock', 'low_stock', etc.)
            'status'        => $statusEnum->value, 
            
            // Return label/color to assist Frontend
            'status_label'  => $statusEnum->label(),
            'status_color'  => $statusEnum->color(),

            'last_updated'  => $this->updated_at?->toIso8601String(),
            'warehouse'     => new WarehouseResource($this->warehouse),
            'variant'       => new ProductVariantResource($this->variant),
        ];
    }
}