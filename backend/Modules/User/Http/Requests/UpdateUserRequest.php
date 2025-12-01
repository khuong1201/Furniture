<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $uuid = $this->route('user');
        return [
            'name' => 'sometimes|string|max:255',
            'email' => "sometimes|email|unique:users,email,{$uuid},uuid", 
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'is_active' => 'boolean',
            'roles' => 'nullable|array',
        ];
    }
}