<?php

declare(strict_types=1);

namespace Modules\Order\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Order\Enums\OrderStatus;
use Illuminate\Validation\Rules\Enum;
use Modules\Order\Domain\Models\Order;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                new Enum(OrderStatus::class),
                function ($attribute, $value, $fail) {
                    $order = Order::where('uuid', $this->route('uuid'))->first();
                    if ($order && $order->status === OrderStatus::DELIVERED) {
                        $fail("Cannot change status of a delivered order.");
                    }
                }
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Vui lòng chọn trạng thái mới.',
            'status.enum' => 'Trạng thái không hợp lệ.',
        ];
    }
}