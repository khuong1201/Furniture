<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Product\Domain\Models\Product;
use Modules\Product\Domain\Models\ProductVariant;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $uuid = $this->route('product');
        $product = Product::where('uuid', $uuid)->firstOrFail(); 
        
        $rules = [
            'name' => 'sometimes|required|string|max:150',
            'category_uuid' => 'sometimes|required|exists:categories,uuid',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'images' => 'nullable|array',
        ];

        if (!$product->has_variants) {
            $rules['price'] = 'required|numeric|min:0';
            $rules['sku'] = [
                'required', 'string', 'max:100',
                Rule::unique('products', 'sku')->ignore($product->id),
                Rule::unique('product_variants', 'sku') 
            ];

            $rules['warehouse_stock'] = 'nullable|array';
            $rules['warehouse_stock.*.warehouse_uuid'] = 'required|exists:warehouses,uuid';
            $rules['warehouse_stock.*.quantity'] = 'required|integer|min:0';
        } 
        
        else {
            $rules['variants'] = 'required|array';

            $rules['variants.*.uuid'] = 'nullable|uuid|exists:product_variants,uuid'; 
            $rules['variants.*.sku'] = 'required|string|distinct';
            $rules['variants.*.price'] = 'required|numeric|min:0';
            
            
            $rules['variants.*.attributes'] = 'nullable|array';
            $rules['variants.*.attributes.*'] = 'exists:attribute_values,uuid';

            $rules['variants.*.stock'] = 'nullable|array';
            $rules['variants.*.stock.*.warehouse_uuid'] = 'required|exists:warehouses,uuid';
            $rules['variants.*.stock.*.quantity'] = 'required|integer|min:0';
        }

        return $rules;
    }
}