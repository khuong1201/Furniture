<?php 

namespace Modules\Product\Http\Requests; 

use Illuminate\Foundation\Http\FormRequest; 
use Illuminate\Validation\Rule;

class UpdateAttributeRequest extends FormRequest {
    
    public function authorize(): bool { return true; }

    public function rules(): array {

        $uuid = $this->route('attribute'); 

        $id = \Modules\Product\Domain\Models\Attribute::where('uuid', $uuid)->value('id');

        return [
            'name' => 'sometimes|required|string|max:255',
            'slug' => ['sometimes', 'required', Rule::unique('attributes', 'slug')->ignore($id)],
            'type' => 'sometimes|required|string|in:text,select,color',
            'values' => 'nullable|array',
            'values.*.uuid' => 'nullable|string|exists:attribute_values,uuid',
            'values.*.value' => 'required|string',
            'values.*.code' => 'nullable|string',
        ];
    }
}