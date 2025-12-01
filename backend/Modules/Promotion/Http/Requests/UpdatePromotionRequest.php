<?php

namespace Modules\Promotion\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePromotionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:percentage,fixed',
            'value' => 'sometimes|numeric|min:0',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'status' => 'sometimes|boolean',
            'product_ids' => 'sometimes|array',
            'product_ids.*' => 'integer|exists:products,id',
        ];
    }
}