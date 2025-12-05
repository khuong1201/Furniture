<?php

declare(strict_types=1);

namespace Modules\Shipping\Services;

use Modules\Shared\Services\BaseService;
use Modules\Shipping\Domain\Repositories\ShippingRepositoryInterface;
use Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use Modules\Order\Domain\Models\Order;
use Modules\Shipping\Events\ShippingStatusUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Model;
use Modules\Order\Enums\OrderStatus;

class ShippingService extends BaseService
{
    public function __construct(
        ShippingRepositoryInterface $repository,
        protected OrderRepositoryInterface $orderRepo
    ) {
        parent::__construct($repository);
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $order = $this->orderRepo->findByUuid($data['order_uuid']);
            if (!$order) throw ValidationException::withMessages(['order_uuid' => 'Order not found']);

            // Chỉ cho phép ship nếu đơn hàng đang xử lý hoặc đã đóng gói
            // Tránh ship đơn đã hủy hoặc đã hoàn thành
            if (in_array($order->status, [OrderStatus::CANCELLED, OrderStatus::DELIVERED])) {
                throw ValidationException::withMessages(['order_uuid' => 'Cannot ship a cancelled or delivered order']);
            }
            
            $shippingData = [
                'order_id' => $order->id,
                'provider' => $data['provider'],
                'tracking_number' => $data['tracking_number'],
                'status' => 'shipped',
                'shipped_at' => now(),
                'fee' => $data['fee'] ?? $this->calculateShippingFee($order), 
            ];

            $shipping = $this->repository->create($shippingData);

            // Cập nhật Order Status sang SHIPPED
            if ($order->status !== OrderStatus::SHIPPED) {
                $order->update([
                    'status' => OrderStatus::SHIPPED,
                    'shipping_status' => 'shipped'
                ]);
            }
            
            event(new ShippingStatusUpdated($shipping));

            return $shipping;
        });
    }

    public function update(string $uuid, array $data): Model
    {
        return DB::transaction(function () use ($uuid, $data) {
            $shipping = $this->findByUuidOrFail($uuid);
            $oldStatus = $shipping->status;

            $shipping->update($data);

            // Nếu trạng thái thay đổi -> Sync ngược lại Order
            if (isset($data['status']) && $data['status'] !== $oldStatus) {
                $this->syncOrderStatus($shipping, $data['status']);
                event(new ShippingStatusUpdated($shipping));
            }

            return $shipping;
        });
    }
    
    protected function syncOrderStatus(Model $shipping, string $newStatus): void
    {
        $order = $shipping->order;
        if (!$order) return;

        switch ($newStatus) {
            case 'delivered':
                $shipping->update(['delivered_at' => now()]);
                $order->update([
                    'status' => OrderStatus::DELIVERED,
                    'shipping_status' => 'delivered',
                ]);
                break;
                
            case 'returned':
                // Hàng hoàn trả -> Order chuyển sang Cancelled hoặc trạng thái riêng Returned
                $order->update([
                    'status' => OrderStatus::CANCELLED, 
                    'shipping_status' => 'returned'
                ]);
                // Lưu ý: Có thể cần logic hoàn kho (Inventory) ở đây hoặc qua Event Listener
                break;
                
            case 'cancelled':
                 $order->update([
                    'shipping_status' => 'not_shipped' // Reset nếu shipping bị hủy
                 ]);
                 break;
        }
    }

    /**
     * Logic tính phí ship đơn giản (dựa trên khối lượng).
     * Có thể mở rộng để gọi API GHTK/GHN sau này.
     */
    public function calculateShippingFee(Order $order): float
    {
        $totalWeight = 0; // Gram

        // Order Items relation cần được load trước đó hoặc lazy load
        foreach ($order->items as $item) {
            $variant = $item->variant; 
            if ($variant) {
                $weight = $variant->weight > 0 ? $variant->weight : 500; 
                $totalWeight += ($weight * $item->quantity);
            }
        }

        // Logic: < 2kg = 30k, mỗi kg tiếp theo + 5k
        if ($totalWeight <= 2000) return 30000;
        
        $extraKg = ceil(($totalWeight - 2000) / 1000);
        return 30000 + ($extraKg * 5000);
    }
}