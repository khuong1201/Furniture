<?php

namespace Modules\Inventory\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Inventory\Domain\Models\Inventory;
use Modules\Inventory\Domain\Repositories\InventoryRepositoryInterface;

class EloquentInventoryRepository
    extends EloquentBaseRepository
    implements InventoryRepositoryInterface
{
    public function __construct(Inventory $model)
    {
        parent::__construct($model);
    }

    public function findByProductAndWarehouse(int $productId, int $warehouseId): ?Inventory
    {
        return $this->model
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();
    }
}
