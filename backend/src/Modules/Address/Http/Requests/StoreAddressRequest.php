<?php

declare(strict_types=1);

namespace Modules\Address\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^(0|\+84)[0-9]{9}$/'],
            'province' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'ward' => ['required', 'string', 'max:100'],
            'street' => ['required', 'string', 'max:255'],
            'is_default' => ['boolean'],
            'type' => ['nullable', 'string', 'in:home,office'],
        ];
    }
}