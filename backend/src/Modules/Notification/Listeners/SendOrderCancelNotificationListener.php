<?php

declare(strict_types=1);

namespace Modules\Notification\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Notification\Services\NotificationService;
use Modules\Order\Events\OrderCancelled;

class SendOrderCancelNotificationListener implements ShouldQueue
{
    public $queue = 'notifications';

    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function handle(OrderCancelled $event): void
    {
        $order = $event->order;

        if (!$order->relationLoaded('user')) {
            $order->load('user');
        }

        $user = $order->user;

        if ($user) {
            $this->notificationService->send(
                $user->id,
                'Order Cancelled',
                "Your order #{$order->uuid} has been cancelled.",
                'warning',
                [
                    'order_uuid' => $order->uuid,
                    'from_status' => $event->fromStatus ?? null,
                ]
            );
        }
    }
}
