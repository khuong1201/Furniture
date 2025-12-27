<?php

declare(strict_types=1);

namespace Modules\Notification\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Order\Events\OrderCreated;
use Modules\Order\Mails\OrderConfirmationMail;
use Modules\Shared\Services\MailService;

class SendOrderEmailListener implements ShouldQueue
{
    public $queue = 'default';

    public $tries = 3;
    public $backoff = 10;

    public function __construct(
        protected MailService $mailService
    ) {}

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        if (!$order->relationLoaded('user')) {
            $order->load('user');
        }

        $user = $order->user;

        if ($user && $user->email) {
            try {
                $isQueued = $this->mailService->sendQueue(
                    $user,
                    new OrderConfirmationMail($order)
                );

                if ($isQueued && method_exists($order, 'logActivity')) {
                    $order->logActivity(
                        event: 'system_notification',
                        description: "Order confirmation email has been sent to {$user->email}.",
                        properties: [
                            'type'     => 'email',
                            'template' => 'order_confirmation',
                        ]
                    );
                }
            } catch (\Throwable $e) {
                Log::error('Failed to send order confirmation email (Attempt): ' . $e->getMessage());
                throw $e;
            }
        }
    }
}
