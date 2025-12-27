<?php

declare(strict_types=1);

namespace Modules\Cart\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // Cho phép 0 để xóa item, hoặc số dương để update
            'quantity' => 'required|integer|min:0', 
        ];
    }
    
    public function messages(): array
    {
        return [
            'quantity.min' => 'Số lượng sản phẩm không hợp lệ.',
            'quantity.integer' => 'Số lượng phải là số nguyên.',
        ];
    }
}