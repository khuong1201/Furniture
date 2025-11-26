<?php

namespace Modules\Address\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['sometimes', 'string', 'max:100'],
            'phone' => ['sometimes', 'string', 'regex:/^(0|\+84)[0-9]{9}$/'],
            'province' => ['sometimes', 'string', 'max:100'],
            'district' => ['sometimes', 'string', 'max:100'],
            'ward' => ['sometimes', 'string', 'max:100'],
            'street' => ['sometimes', 'string', 'max:255'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Số điện thoại không hợp lệ.',
        ];
    }
}
