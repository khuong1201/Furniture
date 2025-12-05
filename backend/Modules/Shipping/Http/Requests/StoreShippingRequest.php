<?php

declare(strict_types=1);

namespace Modules\Shipping\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShippingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'order_uuid' => 'required|exists:orders,uuid', 
            'provider' => 'required|string|max:255',
            'tracking_number' => 'required|string|max:255|unique:shippings,tracking_number',
            'fee' => 'nullable|numeric|min:0',
        ];
    }
}