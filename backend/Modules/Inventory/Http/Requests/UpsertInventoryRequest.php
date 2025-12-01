<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpsertInventoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:0', 
            'min_threshold' => 'nullable|integer|min:0',
            'max_threshold' => 'nullable|integer|min:0',
        ];
    }
}
