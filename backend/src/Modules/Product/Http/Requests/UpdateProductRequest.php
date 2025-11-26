<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $uuid = $this->route('uuid');

        return [
            'name' => 'sometimes|string|max:150',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'sku' => ['sometimes','string','max:100', Rule::unique('products', 'sku')->ignore($uuid, 'uuid'),],
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:100',
            'material' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'warehouse_stock' => 'nullable|array',
            'warehouse_stock.*.warehouse_id' => 'required|exists:warehouses,id',
            'warehouse_stock.*.quantity' => 'required|integer|min:0',
            'status' => 'boolean',
            'images' => 'nullable|array',
            'images.*' => 'file|image|max:5120',
        ];
    }
}