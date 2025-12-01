<?php

namespace Modules\Notification\Listeners;

use Modules\Order\Events\OrderCreated; 
use Modules\Notification\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue; 

class SendOrderSuccessNotification implements ShouldQueue
{
    public function __construct(protected NotificationService $notificationService) {}

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        $this->notificationService->send(
            userId: $order->user_id,
            title: 'Đặt hàng thành công 🎉',
            content: "Đơn hàng #{$order->uuid} của bạn đã được ghi nhận. Tổng tiền: " . number_format($order->total_amount) . " VND.",
            type: 'success',
            data: [
                'order_uuid' => $order->uuid,
                'action_url' => "/orders/{$order->uuid}"
            ]
        );
    }
}