<?php

namespace Modules\Order\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'address_id' => 'required|exists:addresses,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',

            'items.*.variant_uuid' => 'required|exists:product_variants,uuid',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }
}