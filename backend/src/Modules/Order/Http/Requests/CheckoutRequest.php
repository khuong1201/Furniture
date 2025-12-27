<?php

declare(strict_types=1);

namespace Modules\Order\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'address_id'          => 'required|integer|exists:addresses,id',
            'notes'               => 'nullable|string|max:500',
            'voucher_code'        => 'nullable|string|max:50',
            
            // --- THÊM MỚI: Cho phép nhập người nhận khác chủ tài khoản ---
            'consignee_name'      => 'nullable|string|max:255',
            'consignee_phone'     => 'nullable|string|max:20',
            // -------------------------------------------------------------

            'selected_item_uuids'   => 'nullable|array',
            'selected_item_uuids.*' => 'uuid',
        ];
    }
}