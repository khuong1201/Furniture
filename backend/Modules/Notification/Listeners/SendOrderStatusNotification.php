<?php

declare(strict_types=1);

namespace Modules\Notification\Listeners;

use Modules\Order\Events\OrderStatusUpdated;
use Modules\Notification\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderStatusNotification implements ShouldQueue
{
    public $queue = 'default';

    public function __construct(protected NotificationService $notificationService) {}

    public function handle(OrderStatusUpdated $event): void
    {
        $order = $event->order;
        $statusMap = [
            'processing' => 'Đang xử lý',
            'shipped' => 'Đang vận chuyển',
            'delivered' => 'Giao hàng thành công',
            'cancelled' => 'Đã hủy',
        ];

        $statusText = $statusMap[$event->newStatus] ?? $event->newStatus;

        $this->notificationService->send(
            $order->user_id,
            'Cập nhật đơn hàng',
            "Đơn hàng #{$order->uuid} của bạn đã chuyển sang trạng thái: {$statusText}",
            'info',
            ['order_uuid' => $order->uuid]
        );
    }
}