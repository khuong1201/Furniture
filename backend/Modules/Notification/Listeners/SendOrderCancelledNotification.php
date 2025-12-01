<?php

namespace Modules\Notification\Listeners;

use Modules\Order\Events\OrderCancelled;
use Modules\Notification\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderCancelledNotification implements ShouldQueue
{
    public function __construct(protected NotificationService $notificationService) {}

    public function handle(OrderCancelled $event): void
    {
        $order = $event->order;

        $this->notificationService->send(
            userId: $order->user_id,
            title: 'Đơn hàng đã hủy ❌',
            content: "Đơn hàng #{$order->uuid} đã được hủy theo yêu cầu. Kho đã được cập nhật.",
            type: 'warning', 
            data: [
                'order_uuid' => $order->uuid
            ]
        );
    }
}