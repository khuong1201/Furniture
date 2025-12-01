<?php

namespace Modules\Notification\Listeners;

use Modules\Payment\Events\PaymentCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateOrderStatusOnPayment implements ShouldQueue
{
    public function handle(PaymentCompleted $event): void
    {
        $payment = $event->payment;
        $order = $payment->order;

        if ($order && $order->payment_status !== 'paid') {
            $order->update([
                'payment_status' => 'paid',
                'status' => ($order->status === 'pending') ? 'processing' : $order->status
            ]);
            
            // Có thể gửi mail "Thanh toán thành công" tại đây
        }
    }
}