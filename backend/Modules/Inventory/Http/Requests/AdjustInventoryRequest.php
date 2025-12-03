<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjustInventoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'variant_uuid' => 'required|exists:product_variants,uuid',
            'warehouse_uuid' => 'required|exists:warehouses,uuid',
            'quantity' => 'required|integer|not_in:0', 
            'reason' => 'nullable|string|max:255',
        ];
    }
}