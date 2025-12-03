<?php

namespace Modules\Shipping\Services;

use Modules\Shared\Services\BaseService;
use Modules\Shipping\Domain\Repositories\ShippingRepositoryInterface;
use Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use Modules\Order\Domain\Models\Order;
use Modules\Shipping\Events\ShippingStatusUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Model;

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

            if (in_array($order->status, ['cancelled', 'delivered'])) {
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

            if ($order->status !== 'shipped') {
                $order->update([
                    'status' => 'shipped',
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

            if (isset($data['status']) && $data['status'] !== $oldStatus) {
                $order = $shipping->order;

                if ($data['status'] === 'delivered') {
                    $shipping->update(['delivered_at' => now()]);
                    
                    if ($order) {
                        $order->update([
                            'status' => 'delivered',
                            'shipping_status' => 'delivered',
                        ]);
                    }
                } 
                elseif ($data['status'] === 'returned') {
                    if ($order) {
                        $order->update(['status' => 'cancelled', 'shipping_status' => 'returned']);
                    }
                }

                event(new ShippingStatusUpdated($shipping));
            }

            return $shipping;
        });
    }

    public function calculateShippingFee(Order $order): float
    {
        $totalWeight = 0; // Gram

        foreach ($order->items as $item) {
            $variant = $item->variant; 
            
            if ($variant) {
                $weight = $variant->weight > 0 ? $variant->weight : 500; 
                $totalWeight += ($weight * $item->quantity);
            }
        }

        if ($totalWeight <= 2000) return 30000;
        
        $extraKg = ceil(($totalWeight - 2000) / 1000);
        return 30000 + ($extraKg * 5000);
    }
}