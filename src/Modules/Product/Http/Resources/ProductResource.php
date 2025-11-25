<?php

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'category_id' => $this->category_id,
            'sku' => $this->sku,
            'weight' => $this->weight,
            'dimensions' => $this->dimensions,
            'material' => $this->material,
            'color' => $this->color,
            'status' => $this->status,
            'images' => $this->whenLoaded('images', fn() => $this->images),
            'created_at' => $this->created_at,
        ];
    }
}
