<?php

declare(strict_types=1);

namespace Modules\Order\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BuyNowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'variant_uuid' => 'required|string|exists:product_variants,uuid',
            'quantity' => 'required|integer|min:1',
            'address_id' => 'required|integer|exists:addresses,id',
            'voucher_code' => 'nullable|string',
            'notes' => 'nullable|string',
        ];
    }
}