<?php

namespace Modules\Warehouse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'location' => 'nullable|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
        ];
    }
}
