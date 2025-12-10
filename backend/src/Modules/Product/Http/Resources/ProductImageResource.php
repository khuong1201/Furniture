<?php

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'product_id' => $this->product_id,
            'image_url' => $this->image_url,
            'is_primary' => $this->is_primary,
            'created_at' => $this->created_at,
        ];
    }
}
