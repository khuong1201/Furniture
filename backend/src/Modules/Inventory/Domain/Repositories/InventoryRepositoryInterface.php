<?php

namespace Modules\Inventory\Domain\Repositories;

use Modules\Shared\Repositories\BaseRepositoryInterface;
use Modules\Inventory\Domain\Models\Inventory;

interface InventoryRepositoryInterface extends BaseRepositoryInterface
{
    public function findByProductAndWarehouse(int $productId, int $warehouseId): ?Inventory;
}
