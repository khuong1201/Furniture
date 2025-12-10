<?php

declare(strict_types=1);

namespace Modules\Order\Listeners;

// Giả sử Event này tồn tại trong Payment Module
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
        $order = $payment->order ?? null;

        if (!$order) {
            Log::error("Order not found for Payment UUID: {$payment->uuid}");
            return;
        }

        if ($payment->status === 'paid' && $order->payment_status !== 'paid') {
            $updates = ['payment_status' => 'paid'];
            if ($order->status === 'pending') {
                $updates['status'] = 'processing';
            }
            $order->update($updates);
            Log::info("Order {$order->uuid} updated to PAID.");
        }
    }
}