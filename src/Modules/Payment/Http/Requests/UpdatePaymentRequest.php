<?php

namespace Modules\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'method' => 'sometimes|string|max:100',
            'status' => 'sometimes|in:pending,paid,failed,refunded',
            'paid_at' => 'nullable|date',
            'transaction_id' => 'nullable|string|max:255',
        ];
    }
}
