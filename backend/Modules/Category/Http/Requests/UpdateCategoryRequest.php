<?php

namespace Modules\Category\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $uuid = $this->route('category'); 
        
        return [
            'name' => [
                'sometimes', 'string', 'max:100',
            ],
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
        ];
    }
}