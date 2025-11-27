<?php

namespace Modules\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'method' => 'required|string|max:100',
            'status' => 'nullable|in:pending,paid,failed,refunded',
            'paid_at' => 'nullable|date',
            'transaction_id' => 'nullable|string|max:255',
        ];
    }
}
