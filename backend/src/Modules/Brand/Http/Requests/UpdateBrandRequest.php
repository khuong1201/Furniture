<?php

namespace Modules\Brand\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Modules\Brand\Domain\Models\Brand;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation()
    {
        // Nếu user sửa tên mà không truyền slug, tự generate slug mới
        if ($this->has('name') && !$this->has('slug')) {
            $this->merge(['slug' => Str::slug($this->name)]);
        }
    }

    public function rules(): array
    {
        // Lấy UUID từ URL (route param 'brand' do apiResource sinh ra)
        $uuid = $this->route('brand'); 
        
        // Query lấy ID thực để ignore rule unique
        $brandId = Brand::where('uuid', $uuid)->value('id');

        return [
            'name' => [
                'sometimes', 'required', 'string', 'max:100',
                Rule::unique('brands', 'name')->ignore($brandId),
            ],
            'slug' => [
                'sometimes', 'required', 'string',
                Rule::unique('brands', 'slug')->ignore($brandId),
            ],
            'description' => 'nullable|string',
            'logo'        => 'nullable|image|max:2048', // 2MB
            'is_active'   => 'boolean',
            'sort_order'  => 'integer|min:0'
        ];
    }
}