<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $this->route('uuid') . ',uuid',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'avatar_url' => 'nullable|url',
            'is_active' => 'boolean',
        ];
    }
}