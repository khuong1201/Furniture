<?php

namespace Modules\Order\Listeners;

use Modules\Order\Events\OrderStatusUpdated;
use Modules\Order\Enums\OrderStatus;
use Modules\Shipping\Domain\Models\Shipping;
use Illuminate\Support\Str;

class SyncShippingStatus
{
    public function handle(OrderStatusUpdated $event): void
    {
        $order = $event->order;
        $newStatus = $event->newStatus; // 'shipping', 'delivered'...

        switch ($newStatus) {
            case OrderStatus::SHIPPING->value:
                // Tự động tạo hoặc cập nhật bản ghi Shipping khi đơn bắt đầu đi giao
                Shipping::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'uuid' => (string) Str::uuid(),
                        'status' => 'shipped', // Status của bảng shippings
                        'provider' => 'GHTK', // Mặc định hoặc lấy từ config
                        'tracking_number' => 'TRK' . strtoupper(Str::random(10)),
                        'shipped_at' => now(),
                        // Snapshot data lấy từ Order
                        'consignee_name' => $order->shipping_name,
                        'consignee_phone' => $order->shipping_phone,
                        'address_full' => $order->shipping_address_snapshot['full_address'] ?? 'N/A',
                    ]
                );
                // Đồng bộ ngược lại field trên bảng orders
                $order->update(['shipping_status' => 'shipped']);
                break;

            case OrderStatus::DELIVERED->value:
                $shipping = Shipping::where('order_id', $order->id)->first();
                if ($shipping) {
                    $shipping->update([
                        'status' => 'delivered',
                        'delivered_at' => now()
                    ]);
                }
                $order->update(['shipping_status' => 'delivered']);
                break;
        }
    }
}