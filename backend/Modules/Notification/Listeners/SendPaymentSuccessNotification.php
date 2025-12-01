<?php

namespace Modules\Notification\Listeners;

use Modules\Payment\Events\PaymentCompleted;
use Modules\Notification\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPaymentSuccessNotification implements ShouldQueue
{
    public function __construct(protected NotificationService $notificationService) {}

    public function handle(PaymentCompleted $event): void
    {
        $payment = $event->payment;

        if (!$payment->relationLoaded('order')) {
            $payment->load('order');
        }
        
        $order = $payment->order;

        if ($payment->status === 'paid') {
            $amountFormatted = number_format($payment->amount) . ' ' . $payment->currency;
            $method = strtoupper($payment->method);

            $this->notificationService->send(
                userId: $order->user_id,
                title: 'Thanh toán thành công 💸',
                content: "Đơn hàng #{$order->uuid} đã được thanh toán thành công qua {$method}. Số tiền: {$amountFormatted}.",
                type: 'success',
                data: [
                    'order_uuid' => $order->uuid,
                    'payment_uuid' => $payment->uuid,
                    'transaction_id' => $payment->transaction_id,
                    'action_url' => "/orders/{$order->uuid}"
                ]
            );

        } elseif ($payment->status === 'failed') {
             $this->notificationService->send(
                userId: $order->user_id,
                title: 'Thanh toán thất bại ❌',
                content: "Giao dịch thanh toán cho đơn hàng #{$order->uuid} đã thất bại. Vui lòng thử lại.",
                type: 'error',
                data: ['order_uuid' => $order->uuid]
            );
        }
    }
}