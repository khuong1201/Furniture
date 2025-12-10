<?php

declare(strict_types=1);

namespace Modules\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:paid,failed,refunded,pending',
            'transaction_id' => 'nullable|string',
            'payment_data' => 'nullable|array'
        ];
    }
}