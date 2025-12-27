<?php

namespace Modules\Cart\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'variant_uuid' => 'required|string|exists:product_variants,uuid',
            'quantity'     => 'required|integer|min:1',
        ];
    }
}