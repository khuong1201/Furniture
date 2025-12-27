<?php

namespace Modules\Brand\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool { return true; } 

    protected function prepareForValidation()
    {
        if ($this->has('name')) {
            $this->merge(['slug' => Str::slug($this->name)]);
        }
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:100|unique:brands,name',
            'slug'        => 'required|string|unique:brands,slug',
            'description' => 'nullable|string',
            'logo'        => 'nullable|image|max:2048', // 2MB
            'is_active'   => 'boolean',
            'sort_order'  => 'integer|min:0'
        ];
    }
}