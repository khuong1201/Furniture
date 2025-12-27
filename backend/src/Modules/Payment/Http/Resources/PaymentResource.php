<?php

declare(strict_types=1);

namespace Modules\Payment\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Currency\Services\CurrencyService;
use Modules\Order\Enums\PaymentStatus;

class PaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var CurrencyService $currencyService */
        $currencyService = app(CurrencyService::class);

        return [
            'uuid'             => $this->uuid,
            'amount'           => (float) $this->amount,
            // Dùng service format để đồng bộ toàn hệ thống
            'amount_formatted' => $currencyService->format((float) $this->amount),
            
            'status'           => $this->status,
            'status_label'     => $this->getStatusLabel($this->status),
            
            'method'           => strtoupper($this->method),
            'transaction_id'   => $this->transaction_id,
            'payment_data'     => $this->payment_data,

            // Order rút gọn
            'order' => $this->whenLoaded('order', fn() => [
                'uuid' => $this->order->uuid,
                'code' => $this->order->code,
            ]),

            // Gom nhóm ngày tháng cho gọn
            'dates' => [
                'paid_at'    => $this->paid_at ? $this->paid_at->format('Y-m-d H:i') : null,
                'created_at' => $this->created_at->format('Y-m-d H:i'),
                'updated_at' => $this->updated_at->format('Y-m-d H:i'),
            ]
        ];
    }

    private function getStatusLabel($status): string
    {
        // Xử lý nếu $status là Enum object hoặc string
        $val = is_object($status) ? ($status->value ?? 'unknown') : $status;

        return match($val) {
            PaymentStatus::PAID->value      => 'Paid',
            PaymentStatus::UNPAID->value    => 'Unpaid', 
            PaymentStatus::FAILED->value    => 'Failed',
            PaymentStatus::REFUNDED->value  => 'Refunded',
            'pending'                       => 'Pending',
            default                         => ucfirst($val),
        };
    }
}