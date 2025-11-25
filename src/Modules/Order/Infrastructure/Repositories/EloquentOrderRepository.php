<?php

namespace Modules\Order\Infrastructure\Repositories;

use Modules\Order\Domain\Models\Order;
use Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use Modules\Shared\Repositories\EloquentBaseRepository;

class EloquentOrderRepository extends EloquentBaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }
    
    public function findByUuidOrFail(string $uuid): Order
    {
        $order = $this->model->where('uuid', $uuid)->first();

        if (!$order) {
            throw new ModelNotFoundException("Order not found for uuid: {$uuid}");
        }

        // eager load quan hệ để khi show có đầy đủ chi tiết
        $order->load([
            'items.product:id,uuid,name,price',
            'items.warehouse:id,name,code',
            'user:id,name,email',
        ]);

        return $order;
    }
}
