<?php

declare(strict_types=1);

namespace Modules\Warehouse\Domain\Repositories;

use Modules\Shared\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Warehouse\Domain\Models\Warehouse;

interface WarehouseRepositoryInterface extends BaseRepositoryInterface 
{
    public function filter(array $filters): LengthAwarePaginator;
    public function hasStock(int $warehouseId): bool;
    public function findByName(string $name): ?Warehouse;
    public function getDashboardStats(string $uuid): array;
}