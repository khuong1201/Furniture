<?php

namespace Modules\Role\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:roles,name',
            'description' => 'nullable|string|max:500',
            'priority' => 'integer|min:0',
        ];
    }
}

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $uuid = $this->route('role'); 
        
        return [
            'name' => [
                'sometimes', 'string', 'max:100',
                Rule::unique('roles', 'name')->ignore($uuid, 'uuid')
            ],
            'description' => 'nullable|string|max:500',
            'priority' => 'integer|min:0',
        ];
    }
}