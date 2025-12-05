<?php

declare(strict_types=1);

namespace Modules\Voucher\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVoucherRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:vouchers,code|uppercase', // Auto uppercase
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'limit_per_user' => 'required|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'boolean',
        ];
    }
    
    protected function prepareForValidation()
    {
        if ($this->code) {
            $this->merge(['code' => strtoupper($this->code)]);
        }
    }
}