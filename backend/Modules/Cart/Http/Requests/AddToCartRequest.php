<?php

declare(strict_types=1);

namespace Modules\Cart\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'variant_uuid' => 'required|string|uuid|exists:product_variants,uuid',
            'quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'variant_uuid.exists' => 'Sản phẩm (biến thể) không tồn tại.',
            'quantity.min' => 'Số lượng phải lớn hơn 0.',
        ];
    }
}