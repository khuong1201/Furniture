<?php

declare(strict_types=1);

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "AttributeResource",
    title: "Attribute Resource",
    properties: [
        new OA\Property(property: "uuid", type: "string"),
        new OA\Property(property: "name", type: "string"),
        new OA\Property(property: "slug", type: "string"),
        new OA\Property(property: "type", type: "string"),
        new OA\Property(
            property: "values",
            type: "array",
            items: new OA\Items(properties: [
                new OA\Property(property: "value", type: "string"),
                new OA\Property(property: "code", type: "string")
            ])
        )
    ]
)]
class AttributeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'values' => $this->whenLoaded('values', function() {
                return $this->values->map(fn($v) => [
                    'value' => $v->value,
                    'code' => $v->code
                ]);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}