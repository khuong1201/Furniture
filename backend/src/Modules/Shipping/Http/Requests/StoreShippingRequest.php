<?php

namespace Modules\Shipping\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShippingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'order_uuid'      => 'required|exists:orders,uuid', 
            'provider'        => 'required|string|max:50',
            'tracking_number' => 'required|string|max:50|unique:shippings,tracking_number',
            'fee'             => 'nullable|numeric|min:0',
        ];
    }
}