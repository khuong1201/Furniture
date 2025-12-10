<?php

declare(strict_types=1);

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpsertInventoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'variant_uuid' => 'required|exists:product_variants,uuid',
            'warehouse_uuid' => 'required|exists:warehouses,uuid',
            'quantity' => 'required|integer|min:0',
            'min_threshold' => 'nullable|integer|min:0',
        ];
    }
}