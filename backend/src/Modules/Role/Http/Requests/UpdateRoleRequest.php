<?php

declare(strict_types=1);

namespace Modules\Role\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Role\Domain\Models\Role;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $uuid = $this->route('uuid'); 
        
        $roleId = Role::where('uuid', $uuid)->value('id');

        return [
            'name' => [
                'sometimes', 
                'string', 
                'max:100',
                Rule::unique('roles', 'name')->ignore($roleId) 
            ],
            'slug' => [
                'nullable', 
                'string', 
                'max:100',
                Rule::unique('roles', 'slug')->ignore($roleId)
            ],
            'description' => 'nullable|string|max:500',
            'priority'    => 'integer|min:0',
        ];
    }
}