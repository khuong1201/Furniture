<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'     => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6|confirmed',
            'phone'    => 'nullable|string|max:20',
            
            'roles'    => 'nullable|array',
            'roles.*'  => 'exists:roles,name',
        ];
    }
}