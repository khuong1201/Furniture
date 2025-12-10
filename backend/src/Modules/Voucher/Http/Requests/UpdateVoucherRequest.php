<?php

declare(strict_types=1);

namespace Modules\Voucher\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Voucher\Domain\Models\Voucher;
use Illuminate\Validation\Rule;

class UpdateVoucherRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $uuid = $this->route('uuid'); 
        $id = Voucher::where('uuid', $uuid)->value('id');

        return [
            'code' => [
                'sometimes', 'string', 'max:50', 'uppercase',
                Rule::unique('vouchers', 'code')->ignore($id) 
            ],
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|in:fixed,percentage',
            'value' => 'sometimes|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'quantity' => 'sometimes|integer|min:1',
            'limit_per_user' => 'sometimes|integer|min:1',
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