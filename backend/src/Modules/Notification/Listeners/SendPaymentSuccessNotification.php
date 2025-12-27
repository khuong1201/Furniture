<?php

declare(strict_types=1);

namespace Modules\Notification\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Modules\Notification\Services\NotificationService;
use Modules\Payment\Events\PaymentCompleted;

class SendPaymentSuccessNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function handle(PaymentCompleted $event): void
    {
        try {
            $payment = $event->payment;

            $payment->load('order.user');

            $user   = $payment->order->user ?? null;
            $userId = $user?->id;

            if (!$userId) {
                Log::warning("User not found for Payment UUID: {$payment->uuid}");
                return;
            }

            $amount = number_format((float) $payment->amount, 0, ',', '.');

            $title   = 'Payment Successful';
            $content = "Your payment #{$payment->uuid} with the amount of {$amount} VND has been completed successfully.";

            $data = [
                'payment_uuid' => $payment->uuid,
                'order_id'     => $payment->order_id ?? null,
                'amount'       => $payment->amount,
            ];

            $this->notificationService->send(
                userId: $userId,
                title: $title,
                content: $content,
                type: 'payment_success',
                data: $data
            );

            Log::info("Payment notification sent to User ID: {$userId}");

        } catch (\Throwable $e) {
            Log::error('Failed to send payment notification: ' . $e->getMessage());
        }
    }
}