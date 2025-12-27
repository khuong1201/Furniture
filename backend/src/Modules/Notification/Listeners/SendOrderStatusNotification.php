<?php

declare(strict_types=1);

namespace Modules\Notification\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Modules\Notification\Services\NotificationService;
use Modules\Order\Events\OrderStatusUpdated;

class SendOrderStatusNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'notifications';

    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function handle(OrderStatusUpdated $event): void
    {
        try {
            $order = $event->order;

            if (!$order->user_id) {
                return;
            }

            $statusMap = [
                'pending'    => 'Pending',
                'processing' => 'Processing',
                'shipped'    => 'Shipped',
                'delivered'  => 'Delivered',
                'cancelled'  => 'Cancelled',
                'refunded'   => 'Refunded',
            ];

            $statusText = $statusMap[$event->newStatus]
                ?? ucfirst($event->newStatus);

            $type = match ($event->newStatus) {
                'delivered'            => 'success',
                'cancelled', 'refunded'=> 'error',
                'shipped'              => 'warning',
                default                => 'info',
            };

            $this->notificationService->send(
                userId: $order->user_id,
                title: 'Order Status Update',
                content: "Order #{$order->uuid} status updated to: {$statusText}.",
                type: $type,
                data: [
                    'order_uuid' => $order->uuid,
                    'status'     => $event->newStatus,
                    'screen'     => 'order_detail',
                ]
            );
        } catch (\Throwable $e) {
            Log::error('OrderStatusNotification Error: ' . $e->getMessage());
        }
    }
}