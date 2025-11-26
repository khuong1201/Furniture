<?php

namespace Modules\Shipping\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShippingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'provider' => 'required|string|max:255',
            'tracking_number' => 'required|string|max:255|unique:shippings,tracking_number',
            'status' => 'nullable|in:pending,shipped,delivered,cancelled',
        ];
    }
}
