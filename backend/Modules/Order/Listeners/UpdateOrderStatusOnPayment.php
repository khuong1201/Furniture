<?php

namespace Modules\Order\Listeners;

use Modules\Payment\Events\PaymentCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UpdateOrderStatusOnPayment implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(PaymentCompleted $event): void
    {
        $payment = $event->payment;

        if (!$payment->relationLoaded('order')) {
            $payment->load('order');
        }

        $order = $payment->order;

        if (!$order) {
            Log::error("Order not found for Payment UUID: {$payment->uuid}");
            return;
        }
        if ($order->payment_status === 'paid') {
            return;
        }

        if ($payment->status === 'paid') {

            $updates = ['payment_status' => 'paid'];

            if ($order->status === 'pending') {
                $updates['status'] = 'processing';
            }

            $order->update($updates);

            Log::info("Order {$order->uuid} updated to PAID via Payment {$payment->uuid}");
        }
        elseif ($payment->status === 'failed') {
            Log::warning("Payment failed for Order {$order->uuid}");
        }
    }
}