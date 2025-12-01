<?php

namespace Modules\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'order_uuid' => 'required|exists:orders,uuid',
            'method' => 'required|string|in:cod,vnpay,momo,stripe',
        ];
    }
}