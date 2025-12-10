<?php

declare(strict_types=1);

namespace Modules\Warehouse\Domain\Repositories;

use Modules\Shared\Repositories\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface WarehouseRepositoryInterface extends BaseRepositoryInterface 
{
    public function filter(array $filters): LengthAwarePaginator;
    public function hasStock(int $warehouseId): bool;
}