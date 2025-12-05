<?php

declare(strict_types=1);

namespace Modules\Promotion\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePromotionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed',
            // CHANGE: integer vì dùng BigInt
            'value' => 'required|integer|min:0', 
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
            'product_ids' => 'array',
            'product_ids.*' => 'integer|exists:products,id',
            // CHANGE: integer
            'min_order_value' => 'nullable|integer|min:0',
            'max_discount_amount' => 'nullable|integer|min:0',
            'quantity' => 'integer|min:0',
            'limit_per_user' => 'integer|min:1',
        ];
    }
}