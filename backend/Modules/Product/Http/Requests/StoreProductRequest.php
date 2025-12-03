<?php 

namespace Modules\Product\Http\Requests; 

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest {
    
    public function authorize(): bool { return true; }
    
    public function rules(): array {
        return [
            'name' => 'required|string|max:150',
            'category_uuid' => 'required|exists:categories,uuid',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'has_variants' => 'boolean',

            'price' => 'required_if:has_variants,false|numeric|min:0',
            'sku' => 'required_if:has_variants,false|string|unique:products,sku|unique:product_variants,sku',
            'warehouse_stock' => 'required_if:has_variants,false|array',
            'warehouse_stock.*.warehouse_uuid' => 'exists:warehouses,uuid',
            'warehouse_stock.*.quantity' => 'integer|min:0',

            'variants' => 'required_if:has_variants,true|array',
            'variants.*.sku' => 'required|string|distinct|unique:product_variants,sku',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.attributes' => 'required|array', 
            'variants.*.attributes.*' => 'exists:attribute_values,uuid',
            'variants.*.stock' => 'array',
            'variants.*.stock.*.warehouse_uuid' => 'exists:warehouses,uuid',
            'variants.*.stock.*.quantity' => 'integer|min:0',
            'images' => 'nullable|array',
        ];
    }
}