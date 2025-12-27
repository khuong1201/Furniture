<?php

namespace Modules\Media\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'file'       => 'required|file|max:5120|mimes:jpeg,png,jpg,gif,webp,pdf,doc,docx',
            'collection' => 'nullable|string|max:50'
        ];
    }
}