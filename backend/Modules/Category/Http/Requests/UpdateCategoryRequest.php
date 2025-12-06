<?php

declare(strict_types=1);

namespace Modules\Category\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Category\Domain\Models\Category;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $uuid = $this->route('category');
        // Lấy ID từ UUID để ignore unique check
        $id = Category::where('uuid', $uuid)->value('id');

        return [
            'name' => 'sometimes|string|max:100',
            'slug' => [
                'nullable',
                'string',
                'max:150',
                Rule::unique('categories', 'slug')->ignore($id)
            ],
            'description' => 'nullable|string',
            'parent_id' => 'nullable|integer|exists:categories,id',
            'is_active' => 'boolean',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255'
        ];
    }
}