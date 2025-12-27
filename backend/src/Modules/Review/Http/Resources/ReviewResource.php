<?php

declare(strict_types=1);

namespace Modules\Review\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'rating' => (int) $this->rating,
            'comment' => $this->comment,
            'images' => $this->images ?? [],
            'is_verified_purchase' => !is_null($this->order_id),
            'is_approved' => (bool) $this->is_approved, 
            
            'user' => [
                'name' => $this->user->name ?? 'Người dùng ẩn danh',
                'avatar' => $this->user->avatar_url ?? null, 
            ],
            
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}