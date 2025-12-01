<?php

namespace Modules\Cart\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'product_uuid' => 'required|string|uuid|exists:products,uuid',
            'quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'product_uuid.exists' => 'Sản phẩm không tồn tại.',
            'quantity.min' => 'Số lượng phải lớn hơn 0.',
        ];
    }
}