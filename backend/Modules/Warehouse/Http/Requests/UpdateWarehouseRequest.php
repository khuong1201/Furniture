<?php

namespace Modules\Warehouse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class UpdateWarehouseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $uuid = $this->route('warehouse'); 
        
        return [
            'name' => 'sometimes|string|max:150',
            'location' => 'nullable|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
        ];
    }
}
