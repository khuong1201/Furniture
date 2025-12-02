<?php

namespace Modules\Collection\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCollectionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $uuid = $this->route('uuid'); 

        return [
            'name' => 'sometimes|required|string|max:255',
            'slug' => [
                'sometimes', 'required', 'string', 'max:255',
                Rule::unique('collections', 'slug')->ignore($uuid, 'uuid')
            ],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:products,id',
        ];
    }
}