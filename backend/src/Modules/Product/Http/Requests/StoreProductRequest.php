<?php
namespace Modules\Product\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreProductRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'name' => 'required|string|max:150',
            'category_uuid' => 'required|exists:categories,uuid',
            'has_variants' => 'boolean',
            'price' => 'required_if:has_variants,false|numeric|min:0',
            'sku' => 'required_if:has_variants,false|string|unique:products,sku',
            'variants' => 'required_if:has_variants,true|array',
            'images' => 'nullable|array'
        ];
    }
}