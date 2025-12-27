<?php

declare(strict_types=1);

namespace Modules\Notification\Listeners;

use Modules\Order\Events\OrderCreated;
use Modules\Notification\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderCreateNotificationListener implements ShouldQueue
{
    public $queue = 'notifications';

    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        
        if (!$order->relationLoaded('user')) {
            $order->load('user');
        }
        $user = $order->user;

        if ($user) {
            $this->notificationService->send(
                $user->id,
                'Order Created Successfully',
                "Your order #{$order->uuid} has been successfully created.",
                'success',
                ['order_uuid' => $order->uuid]
            );
        }
    }
}