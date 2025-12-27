<?php

declare(strict_types=1);

namespace Modules\Review\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'product_uuid' => 'required|string|exists:products,uuid', 
            'rating'       => 'required|integer|min:1|max:5',
            'comment'      => 'nullable|string|max:1000',
            'images'       => 'nullable|array|max:5', // Max 5 ảnh
            'images.*'     => 'string|url'
        ];
    }
}