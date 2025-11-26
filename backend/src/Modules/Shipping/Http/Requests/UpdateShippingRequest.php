<?php

namespace Modules\Shipping\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShippingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider' => 'sometimes|string|max:255',
            'tracking_number' => 'sometimes|string|max:255|unique:shippings,tracking_number,' . $this->uuid . ',uuid',
            'status' => 'sometimes|in:pending,shipped,delivered,cancelled',
            'shipped_at' => 'nullable|date',
            'delivered_at' => 'nullable|date',
        ];
    }
}
