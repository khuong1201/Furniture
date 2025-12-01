<?php

namespace Modules\Category\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
        ];
    }
}