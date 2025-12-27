<?php

declare(strict_types=1);

namespace Modules\Category\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "CategoryResource", 
    title: "Category Resource",
    description: "Cấu trúc dữ liệu trả về của Danh mục",
    properties: [
        new OA\Property(property: "uuid", type: "string", format: "uuid", example: "550e8400-e29b-41d4-a716-446655440000"),
        new OA\Property(property: "name", type: "string", example: "Laptop Gaming"),
        new OA\Property(property: "slug", type: "string", example: "laptop-gaming"),
        new OA\Property(property: "image", type: "string", example: "https://cdn.example.com/laptop.png", nullable: true),
        new OA\Property(property: "description", type: "string", nullable: true),
        new OA\Property(property: "parent_id", type: "integer", nullable: true),
        new OA\Property(property: "is_active", type: "boolean", example: true),
        new OA\Property(
            property: "children",
            type: "array",
            items: new OA\Items(ref: "#/components/schemas/CategoryResource"),
            nullable: true
        ),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
    ]
)]
class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'        => $this->uuid,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'image'       => $this->image,
            'description' => $this->description,
            'parent_id'   => $this->parent_id,
            'is_active'   => $this->is_active,
            'children'    => CategoryResource::collection($this->whenLoaded('children')),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}