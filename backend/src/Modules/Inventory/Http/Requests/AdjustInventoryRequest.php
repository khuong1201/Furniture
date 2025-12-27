<?php

declare(strict_types=1);

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjustInventoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'inventory_uuid' => ['required', 'uuid', 'exists:inventory_stocks,uuid'],
            'quantity' => ['required', 'integer'],
            'reason' => ['nullable', 'string'],
        ];
    }

}