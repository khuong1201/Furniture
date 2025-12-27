<?php

declare(strict_types=1);

namespace Modules\Shipping\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Order\Domain\Models\Order;
use Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use Modules\Order\Enums\OrderStatus; 
use Modules\Shipping\Domain\Repositories\ShippingRepositoryInterface;
use Modules\Shipping\Events\ShippingStatusUpdated;
use Modules\Shared\Exceptions\BusinessException;
use Modules\Shared\Services\BaseService;

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
            /** @var Order $order */
            $order = $this->orderRepo->findByUuid($data['order_uuid']);
            
            if (!$order) {
                throw new BusinessException(404130, 'Order not found'); 
            }

            // Logic: Không ship đơn đã hủy hoặc đã giao
            if (in_array($order->status, [OrderStatus::CANCELLED, OrderStatus::DELIVERED])) {
                throw new BusinessException(422202, 'Cannot ship a cancelled or delivered order');
            }

            // Logic: Check duplicate tracking
            if ($this->repository->query()->where('tracking_number', $data['tracking_number'])->exists()) {
                throw new BusinessException(409201, 'Tracking number already exists');
            }

            // SNAPSHOT DATA: Lấy địa chỉ từ Order lưu sang Shipping
            // Giả sử Order có relationship 'address' hoặc các field address
            $shippingAddress = $order->shipping_address ?? $order->address ?? 'N/A';
            $consigneeName   = $order->shipping_name ?? $order->user->name ?? 'N/A';
            $consigneePhone  = $order->shipping_phone ?? $order->user->phone ?? 'N/A';

            $shippingData = [
                'order_id'        => $order->id,
                'provider'        => $data['provider'],
                'tracking_number' => $data['tracking_number'],
                'status'          => 'shipped',
                'shipped_at'      => now(),
                'fee'             => $data['fee'] ?? $this->calculateShippingFee($order),
                // Snapshot fields
                'consignee_name'  => $consigneeName,
                'consignee_phone' => $consigneePhone,
                'address_full'    => $shippingAddress,
            ];

            $shipping = $this->repository->create($shippingData);

            // Update Order Status
            if ($order->status !== OrderStatus::SHIPPED) {
                $order->update([
                    'status'          => OrderStatus::SHIPPING,
                    'shipping_status' => 'shipped'
                ]);
            }

            event(new ShippingStatusUpdated($shipping));

            return $shipping;
        });
    }

    public function update(string $uuid, array $data): Model
    {
        $shipping = $this->findByUuidOrFail($uuid);

        return DB::transaction(function () use ($shipping, $data) {
            $oldStatus = $shipping->status;

            // Check duplicate tracking nếu có đổi
            if (isset($data['tracking_number']) && $data['tracking_number'] !== $shipping->tracking_number) {
                if ($this->repository->query()->where('tracking_number', $data['tracking_number'])->exists()) {
                    throw new BusinessException(409201, 'Tracking number exists');
                }
            }

            $shipping->update($data);

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
                $order->update([
                    'status'          => OrderStatus::DELIVERED,
                    'shipping_status' => 'delivered',
                ]);
                $shipping->update(['delivered_at' => now()]);
                break;
                
            case 'returned':
                // Tùy nghiệp vụ: Hoàn hàng có thể về Cancelled hoặc Refunded
                $order->update([
                    'status'          => OrderStatus::CANCELLED, 
                    'shipping_status' => 'returned'
                ]);
                break;
                
            case 'cancelled':
                 $order->update([
                    'shipping_status' => 'not_shipped'
                    // Có thể revert status order về Confirmed/Processing nếu cần
                 ]);
                 break;
        }
    }

    public function calculateShippingFee(Order $order): float
    {
        // Logic tính phí Dummy, nên thay bằng API tính phí thật
        $totalWeight = 0; 
        
        // Eager loading check
        if ($order->relationLoaded('items')) {
            foreach ($order->items as $item) {
                $variant = $item->variant; 
                $weight = ($variant && $variant->weight > 0) ? $variant->weight : 500;
                $totalWeight += ($weight * $item->quantity);
            }
        } else {
             $totalWeight = 1000; // Default fallback
        }

        if ($totalWeight <= 2000) return 30000;
        $extraKg = ceil(($totalWeight - 2000) / 1000);
        return 30000 + ($extraKg * 5000);
    }
}