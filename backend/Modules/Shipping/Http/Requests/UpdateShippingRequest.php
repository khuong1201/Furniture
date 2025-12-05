<?php

declare(strict_types=1);

namespace Modules\Shipping\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Shipping\Domain\Models\Shipping;
use Illuminate\Validation\Rule;

class UpdateShippingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $uuid = $this->route('shipping'); 
        $id = Shipping::where('uuid', $uuid)->value('id');

        return [
            'provider' => 'sometimes|string|max:255',
            'tracking_number' => [
                'sometimes', 'string', 'max:255',
                Rule::unique('shippings', 'tracking_number')->ignore($id)
            ],
            'status' => 'sometimes|in:pending,shipped,delivered,cancelled,returned',
        ];
    }
}