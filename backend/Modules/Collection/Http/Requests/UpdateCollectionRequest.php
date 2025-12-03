<?php

namespace Modules\Collection\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Collection\Domain\Models\Collection;

class UpdateCollectionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $uuid = $this->route('collection');
        $id = Collection::where('uuid', $uuid)->value('id');

        return [
            'name' => 'sometimes|string|max:255',
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('collections', 'slug')->ignore($id)],
            'description' => 'nullable|string',
            'banner_image' => 'nullable|string',
            'is_active' => 'boolean',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:products,id',
        ];
    }
}