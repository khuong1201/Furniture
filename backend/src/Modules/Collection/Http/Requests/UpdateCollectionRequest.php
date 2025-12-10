<?php

declare(strict_types=1);

namespace Modules\Collection\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Collection\Domain\Models\Collection;

class UpdateCollectionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $uuid = $this->route('collection') ?? $this->route('uuid');
        $id = Collection::where('uuid', $uuid)->value('id');

        return [
            'name' => 'sometimes|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('collections', 'slug')->ignore($id)],
            'description' => 'nullable|string',
            'banner_image' => 'nullable|image|max:5120',
            'is_active' => 'boolean',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:products,id',
        ];
    }
}