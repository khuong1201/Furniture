<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'sku' => 'required|string|max:100|unique:products,sku',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:100',
            'material' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'status' => 'boolean',
            'warehouse_stock' => 'required|array|min:1',
            'warehouse_stock.*.warehouse_id' => 'required|exists:warehouses,id',
            'warehouse_stock.*.quantity' => 'required|integer|min:0',
            'images' => 'nullable|array',
            'images.*' => 'file|image|max:5120',
        ];
    }
}