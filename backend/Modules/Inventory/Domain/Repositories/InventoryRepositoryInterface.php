<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Repositories;

use Modules\Shared\Repositories\BaseRepositoryInterface;
use Modules\Inventory\Domain\Models\InventoryStock;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InventoryRepositoryInterface extends BaseRepositoryInterface
{
    public function findByVariantAndWarehouse(int $variantId, int $warehouseId, bool $lock = false): ?InventoryStock;
    public function filter(array $filters): LengthAwarePaginator;
}