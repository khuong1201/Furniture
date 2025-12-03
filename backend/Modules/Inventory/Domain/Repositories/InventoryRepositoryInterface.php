<?php

namespace Modules\Inventory\Domain\Repositories;

use Modules\Shared\Repositories\BaseRepositoryInterface;
use Modules\Inventory\Domain\Models\InventoryStock;

interface InventoryRepositoryInterface extends BaseRepositoryInterface
{
    public function findByVariantAndWarehouse(int $variantId, int $warehouseId, bool $lock = false): ?InventoryStock;
}