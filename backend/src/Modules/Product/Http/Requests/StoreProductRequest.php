<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $rules = [
            'name'          => 'required|string|max:150',
            'slug'          => 'nullable|string|max:150|unique:products,slug',
            'category_uuid' => 'required|exists:categories,uuid',
            'brand_uuid'    => 'nullable|exists:brands,uuid',
            'has_variants'  => 'boolean',
            'description'   => 'nullable|string',
            'is_active'     => 'boolean',
            'images'        => 'nullable|array',
            'images.*'      => 'image|max:5120',
        ];

        if ($this->boolean('has_variants')) {
            $rules['variants'] = 'required|array|min:1';
            $rules['variants.*.sku'] = 'required|string|distinct';
            $rules['variants.*.price'] = 'required|integer|min:0';
            
            $rules['variants.*.attributes'] = 'nullable|array';
            $rules['variants.*.attributes.*.attribute_slug'] = 'required|string|exists:attributes,slug';
            $rules['variants.*.attributes.*.value'] = 'required|string';
        } else {
            $rules['price'] = 'required|integer|min:0';
            $rules['sku']   = 'required|string|unique:products,sku';
        }

        return $rules;
    }
}