<?php 

namespace Modules\Product\Http\Requests; 

use Illuminate\Foundation\Http\FormRequest;

class StoreAttributeRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:attributes,slug',
            'type' => 'required|string|in:text,select,color',
            'values' => 'nullable|array',
            'values.*.value' => 'required|string',
            'values.*.code' => 'nullable|string',
        ];
    }
}