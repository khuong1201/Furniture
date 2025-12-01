<?php

namespace Modules\Permission\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:permissions,name',
            'description' => 'nullable|string|max:255',
            'module' => 'nullable|string|max:50',
        ];
    }
}