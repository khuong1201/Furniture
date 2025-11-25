<?php

namespace Modules\Log\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => 'sometimes|string|max:255',
            'model' => 'nullable|string|max:255',
            'model_id' => 'nullable|integer',
            'payload' => 'nullable|array',
        ];
    }
}
