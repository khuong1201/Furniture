<?php

declare(strict_types=1);

namespace Modules\Order\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'user_id'      => 'required|integer|exists:users,id',
            'address_id'   => 'required|integer|exists:addresses,id',
            'notes'        => 'nullable|string',
            'voucher_code' => 'nullable|string',

            // --- THÊM MỚI ---
            'consignee_name'  => 'nullable|string|max:255',
            'consignee_phone' => 'nullable|string|max:20',
            
            'items' => 'required|array|min:1',
            'items.*.variant_uuid' => 'required|string|exists:product_variants,uuid',
            'items.*.quantity'     => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Vui lòng chọn khách hàng.',
            'address_id.required' => 'Vui lòng chọn địa chỉ giao hàng.',
            'items.required' => 'Đơn hàng phải có ít nhất 1 sản phẩm.',
        ];
    }
}