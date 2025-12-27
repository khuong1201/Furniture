<?php

declare(strict_types=1);

namespace Modules\Brand\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "BrandResource",
    title: "Brand Resource",
    properties: [
        new OA\Property(property: "uuid", type: "string", format: "uuid"),
        new OA\Property(property: "name", type: "string", example: "Nike"),
        new OA\Property(property: "slug", type: "string", example: "nike"),
        new OA\Property(property: "logo_url", type: "string", nullable: true),
        new OA\Property(property: "description", type: "string", nullable: true),
        new OA\Property(property: "is_active", type: "boolean"),
        new OA\Property(property: "sort_order", type: "integer"),
    ]
)]
class BrandResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'uuid'        => $this->uuid,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'logo_url'    => $this->logo_url,
            'description' => $this->description,
            'is_active'   => $this->is_active,
            'sort_order'  => $this->sort_order,
            'created_at'  => $this->created_at->toIso8601String(),
        ];
    }
}