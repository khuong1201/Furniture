<?php

declare(strict_types=1);

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $uuid = $this->route('uuid'); 

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes', 
                'email', 
                Rule::unique('users', 'email')->ignore($uuid, 'uuid')
            ],
            'phone' => [
                'nullable', 
                'string', 
                'max:20',
                Rule::unique('users', 'phone')->ignore($uuid, 'uuid')
            ],
            'password' => ['nullable', 'string', 'confirmed', Password::min(6)],
            'avatar_url' => ['nullable', 'url', 'max:2048'],
            'is_active' => ['boolean'],

            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ];
    }
}