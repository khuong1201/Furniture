<?php

namespace Modules\Notification\Listeners;

use Modules\Shipping\Events\ShippingStatusUpdated;
use Modules\Notification\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendShippingUpdateNotification implements ShouldQueue
{
    public function __construct(protected NotificationService $notificationService) {}

    public function handle(ShippingStatusUpdated $event): void
    {
        $shipping = $event->shipping;
        
        if (!$shipping->relationLoaded('order')) {
            $shipping->load('order');
        }
        
        $order = $shipping->order;
        $trackingNumber = $shipping->tracking_number;
        $provider = $shipping->provider;

        $title = '';
        $content = '';
        $type = 'info';

        switch ($shipping->status) {
            case 'shipped':
                $title = 'Đơn hàng đang được vận chuyển 🚚';
                $content = "Đơn hàng #{$order->uuid} đã được giao cho đối tác {$provider}. Mã vận đơn: {$trackingNumber}.";
                $type = 'info';
                break;

            case 'delivered':
                $title = 'Giao hàng thành công 🎉';
                $content = "Đơn hàng #{$order->uuid} đã được giao thành công. Hãy đánh giá sản phẩm nhé!";
                $type = 'success';
                break;

            case 'cancelled':
                $title = 'Vận chuyển bị hủy ⚠️';
                $content = "Quá trình vận chuyển đơn hàng #{$order->uuid} gặp sự cố hoặc đã bị hủy.";
                $type = 'warning';
                break;
            
            default:
                return; 
        }

        $this->notificationService->send(
            userId: $order->user_id,
            title: $title,
            content: $content,
            type: $type,
            data: [
                'order_uuid' => $order->uuid,
                'shipping_uuid' => $shipping->uuid,
                'tracking_number' => $trackingNumber,
                'action_url' => "/orders/{$order->uuid}"
            ]
        );

    }
}