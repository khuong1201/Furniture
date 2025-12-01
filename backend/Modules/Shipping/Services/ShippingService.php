<?php

namespace Modules\Shipping\Services;

use Modules\Shared\Services\BaseService;
use Modules\Shipping\Domain\Repositories\ShippingRepositoryInterface;
use Modules\Order\Domain\Repositories\OrderRepositoryInterface;
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
            $order = \Modules\Order\Models\Order::where('uuid', $data['order_uuid'])->first();
            
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
            $shipping = $this->repository->findByUuid($uuid);
            
            $oldStatus = $shipping->status;
            $shipping->update($data);

            if (isset($data['status']) && $data['status'] !== $oldStatus) {
                $order = $shipping->order;

                if ($data['status'] === 'delivered') {
                    $shipping->update(['delivered_at' => now()]);
                    
                    $order->update([
                        'status' => 'delivered',
                        'shipping_status' => 'delivered',
                    ]);
                } elseif ($data['status'] === 'cancelled') {
                }
                event(new ShippingStatusUpdated($shipping));
            }

            return $shipping;
        });
    }
}