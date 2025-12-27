<?php

declare(strict_types=1);

namespace Modules\Order\Listeners;

use Modules\Payment\Events\PaymentCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Enums\PaymentStatus;

class UpdateOrderStatusOnPayment implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(PaymentCompleted $event): void
    {
        $payment = $event->payment;
        // Eager load order nếu chưa có để tránh N+1
        $order = $payment->order;

        if (!$order) {
            Log::error("Listener: Order not found for Payment UUID: {$payment->uuid}");
            return;
        }

        switch ($payment->status) {
            
            // 1. THANH TOÁN THÀNH CÔNG
            case 'paid':
                if ($order->payment_status !== PaymentStatus::PAID->value) {
                    $updateData = ['payment_status' => PaymentStatus::PAID->value];
                    
                    // Chỉ tự động chuyển sang PROCESSING nếu đơn đang ở PENDING
                    // Tránh ghi đè nếu đơn đã sang SHIPPING hoặc DELIVERED
                    if ($order->status->value === OrderStatus::PENDING->value) {
                        $updateData['status'] = OrderStatus::PROCESSING->value;
                    }
                    
                    $order->update($updateData);
                    Log::info("Order {$order->code}: Payment confirmed via Gateway.");
                }
                break;

            // 2. THANH TOÁN THẤT BẠI
            case 'failed':
                if ($order->payment_status !== PaymentStatus::FAILED->value) {
                    $order->update([
                        'payment_status' => PaymentStatus::FAILED->value,
                    ]);
                    Log::warning("Order {$order->code}: Payment failed.");
                }
                break;

            // 3. HOÀN TIỀN (Refund)
            case 'refunded':
                if ($order->payment_status !== PaymentStatus::REFUNDED->value) {
                    $order->update([
                        'payment_status' => PaymentStatus::REFUNDED->value,
                        // Khi refund, bắt buộc hủy đơn nếu chưa giao xong
                        'status'         => OrderStatus::CANCELLED->value,
                        'shipping_status'=> 'cancelled' 
                    ]);
                    Log::info("Order {$order->code}: Payment refunded & Order cancelled.");
                }
                break;
        }
    }
}