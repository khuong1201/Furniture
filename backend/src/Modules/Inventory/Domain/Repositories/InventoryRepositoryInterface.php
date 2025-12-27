<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Repositories;

use Modules\Shared\Contracts\BaseRepositoryInterface;
use Modules\Inventory\Domain\Models\InventoryStock;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InventoryRepositoryInterface extends BaseRepositoryInterface
{
    public function findByVariantAndWarehouse(int $variantId, int $warehouseId, bool $lock = false): ?InventoryStock;
    
    public function filter(array $filters): LengthAwarePaginator;

    public function findStockForAllocation(int $variantId, int $quantity): ?InventoryStock;

    public function sumQuantityByVariant(int $variantId): int;

    public function getMovementChartData(?int $warehouseId, string $period, ?int $month = null, ?int $year = null): array;

    public function getDashboardMetrics(?int $warehouseId): array;
    
    public function logChange(array $data): void;
}