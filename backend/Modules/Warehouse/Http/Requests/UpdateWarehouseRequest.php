<?php

declare(strict_types=1);

namespace Modules\Warehouse\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Warehouse\Domain\Models\Warehouse;
use Illuminate\Validation\Rule;

class UpdateWarehouseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $uuid = $this->route('warehouse'); 
        $id = Warehouse::where('uuid', $uuid)->value('id');
        
        return [
            'name' => ['sometimes', 'string', 'max:150', Rule::unique('warehouses', 'name')->ignore($id)],
            'location' => 'nullable|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean'
        ];
    }
}