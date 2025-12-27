<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductImageRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'image'      => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'is_primary' => 'boolean'
        ];
    }
}