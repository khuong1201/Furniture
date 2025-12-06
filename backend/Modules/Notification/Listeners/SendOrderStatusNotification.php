<?php

declare(strict_types=1);

namespace Modules\Notification\Listeners;

use Modules\Order\Events\OrderStatusUpdated;
use Modules\Notification\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOrderStatusNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'notifications';

    public function __construct(protected NotificationService $notificationService) {}

    public function handle(OrderStatusUpdated $event): void
    {
        try {
            $order = $event->order;

            // Chỉ gửi nếu đơn hàng thuộc về user đăng ký
            if (!$order->user_id) {
                return;
            }

            $statusMap = [
                'pending'    => 'Chờ xử lý',
                'processing' => 'Đang xử lý',
                'shipped'    => 'Đang vận chuyển',
                'delivered'  => 'Giao hàng thành công',
                'cancelled'  => 'Đã hủy',
                'refunded'   => 'Đã hoàn tiền',
            ];

            $statusText = $statusMap[$event->newStatus] ?? ucfirst($event->newStatus);

            $type = match ($event->newStatus) {
                'delivered' => 'success',
                'cancelled', 'refunded' => 'error',
                'shipped' => 'warning',
                default => 'info'
            };

            $this->notificationService->send(
                userId: $order->user_id,
                title: 'Cập nhật đơn hàng',
                content: "Đơn #{$order->uuid} trạng thái mới: {$statusText}",
                type: $type,
                data: [
                    'order_uuid' => $order->uuid,
                    'status' => $event->newStatus,
                    'screen' => 'order_detail'
                ]
            );
        } catch (\Throwable $e) {
            Log::error("OrderStatusNotification Error: " . $e->getMessage());
        }
    }
}