<?php

namespace Modules\Shipping\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShippingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'tracking_number' => 'nullable|string|max:50',
            'provider'        => 'nullable|string|max:50',
            'status'          => 'required|string|in:pending,shipped,delivered,returned,cancelled',
            'fee'             => 'nullable|numeric|min:0',
            'shipped_at'      => 'nullable|date',
            'delivered_at'    => 'nullable|date|after_or_equal:shipped_at',
        ];
    }
}