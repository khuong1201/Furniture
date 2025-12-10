<?php

declare(strict_types=1);

namespace Modules\Category\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'parent_id' => $this->parent_id, // Có thể thay bằng parent_uuid nếu muốn giấu ID tăng tự động
            
            'is_active' => $this->is_active,
            
            // Xử lý đệ quy cho cấu trúc cây (Tree)
            // whenLoaded('allChildren') khớp với relation trong Repository getTree()
            'children' => CategoryResource::collection($this->whenLoaded('allChildren')),
            
            // Nếu chỉ load cấp con trực tiếp (cho admin view list thông thường)
            'direct_children' => CategoryResource::collection($this->whenLoaded('children')),

            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}