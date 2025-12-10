<?php

declare(strict_types=1);

namespace Modules\Currency\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Currency\Domain\Models\Currency;
use Illuminate\Validation\Rule;

class UpdateCurrencyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $uuid = $this->route('uuid'); 
        $id = Currency::where('uuid', $uuid)->value('id');

        return [
            'code' => ['sometimes', 'string', 'size:3', 'uppercase', Rule::unique('currencies', 'code')->ignore($id)],
            'name' => 'sometimes|string|max:50',
            'symbol' => 'sometimes|string|max:10',
            'exchange_rate' => 'sometimes|numeric|min:0',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}