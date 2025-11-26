<?php

namespace Modules\Order\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => 'nullable|exists:addresses,id',
            'items' => 'sometimes|array|min:1',
            'items.*.product_uuid' => 'required_with:items|exists:products,uuid',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            // 'items.*.unit_price' => 'required_with:items|numeric|min:0',
        ];
    }
}
