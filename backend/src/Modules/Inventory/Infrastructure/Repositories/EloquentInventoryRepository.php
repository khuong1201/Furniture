<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Repositories;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Modules\Inventory\Domain\Models\InventoryLog;
use Modules\Inventory\Domain\Models\InventoryStock;
use Modules\Inventory\Domain\Repositories\InventoryRepositoryInterface;
use Modules\Shared\Repositories\EloquentBaseRepository;

class EloquentInventoryRepository extends EloquentBaseRepository implements InventoryRepositoryInterface
{
    public function __construct(InventoryStock $model)
    {
        parent::__construct($model);
    }

    public function findByVariantAndWarehouse(int $variantId, int $warehouseId, bool $lock = false): ?InventoryStock
    {
        $query = $this->model->newQuery()
            ->where('product_variant_id', $variantId)
            ->where('warehouse_id', $warehouseId);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function findStockForAllocation(int $variantId, int $quantity): ?InventoryStock
    {
        return $this->model->newQuery()
            ->where('product_variant_id', $variantId)
            ->where('quantity', '>=', $quantity)
            ->orderByDesc('quantity')
            ->lockForUpdate()
            ->first();
    }

    public function sumQuantityByVariant(int $variantId): int
    {
        return (int) $this->model->newQuery()
            ->where('product_variant_id', $variantId)
            ->sum('quantity');
    }

    public function filter(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with([
            'variant.product',
            'variant.attributeValues.attribute',
            'warehouse'
        ]);

        if (!empty($filters['warehouse_uuid'])) {
            $query->whereHas('warehouse', fn($q) => $q->where('uuid', $filters['warehouse_uuid']));
        }

        if (!empty($filters['search'])) {
            $q = $filters['search'];
            $query->whereHas('variant', function (Builder $v) use ($q) {
                $v->where('sku', 'like', "%{$q}%")
                ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%{$q}%"));
            });
        }

        if (!empty($filters['status'])) {
            switch ($filters['status']) {
                case 'out_of_stock':
                    $query->where('quantity', '<=', 0);
                    break;
                case 'low_stock':
                    $query->where('quantity', '>', 0)
                        ->whereRaw('quantity <= COALESCE(min_threshold, 10)');
                    break;
                case 'in_stock':
                    $query->whereRaw('quantity > COALESCE(min_threshold, 10)');
                    break;
                case 'old_stock':
                    $query->where('quantity', '>', 0)
                        ->where('updated_at', '<=', Carbon::now()->subDays(90));
                    break;
            }
        }

        $sort = $filters['sort'] ?? 'latest';
        switch ($sort) {
            case 'qty_asc': $query->orderBy('quantity', 'asc'); break;
            case 'qty_desc': $query->orderBy('quantity', 'desc'); break;
            default: $query->latest();
        }

        return $query->paginate($filters['per_page'] ?? 20);
    }

    // Chart: Lịch sử biến động (Line Chart)
    public function getMovementChartData(?int $warehouseId, string $period, ?int $month = null, ?int $year = null): array
    {
        $query = InventoryLog::query();

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        
        $now = Carbon::now();
        $targetYear = $year ?: $now->year;
        $targetMonth = $month ?: $now->month;

        if ($period === 'year') {
            $query->whereYear('created_at', $targetYear);
            $groupByFormat = "%Y-%m";
        } elseif ($period === 'month') {
            $query->whereYear('created_at', $targetYear)
                  ->whereMonth('created_at', $targetMonth);
            $groupByFormat = "%Y-%m-%d";
        } else {
            $query->where('created_at', '>=', $now->subDays(6)->startOfDay());
            $groupByFormat = "%Y-%m-%d";
        }

        return $query->selectRaw("
                DATE_FORMAT(created_at, '$groupByFormat') as time_unit,
                SUM(CASE WHEN quantity_change > 0 THEN quantity_change ELSE 0 END) as import_qty,
                SUM(CASE WHEN quantity_change < 0 THEN ABS(quantity_change) ELSE 0 END) as export_qty
            ")
            ->groupBy('time_unit')
            ->orderBy('time_unit', 'ASC')
            ->get()
            ->toArray(); 
    }

    // Dashboard: Cards & In/Out tháng hiện tại
    public function getDashboardMetrics(?int $warehouseId): array
    {
        // 1. Cards Query (State hiện tại)
        $stockQuery = $this->model->newQuery();
        if ($warehouseId) {
            $stockQuery->where('warehouse_id', $warehouseId);
        }

        // Old Stock: > 90 ngày chưa update
        $oldStockDate = Carbon::now()->subDays(90);

        $cards = $stockQuery->selectRaw("
            COUNT(DISTINCT product_variant_id) as total_skus,
            COALESCE(SUM(quantity), 0) as total_items,
            SUM(CASE WHEN quantity <= 0 THEN 1 ELSE 0 END) as out_of_stock_count,
            SUM(CASE WHEN quantity > 0 AND quantity <= COALESCE(min_threshold, 10) THEN 1 ELSE 0 END) as low_stock_count,
            SUM(CASE WHEN updated_at <= ? AND quantity > 0 THEN 1 ELSE 0 END) as old_stock_count
        ", [$oldStockDate])->first()->toArray();

        // 2. Movements Query (Chỉ tháng hiện tại)
        $logQuery = InventoryLog::query()
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year);

        if ($warehouseId) {
            $logQuery->where('warehouse_id', $warehouseId);
        }

        $movements = $logQuery->selectRaw("
            COALESCE(SUM(CASE WHEN quantity_change > 0 THEN quantity_change ELSE 0 END), 0) as inbound,
            COALESCE(SUM(CASE WHEN quantity_change < 0 THEN ABS(quantity_change) ELSE 0 END), 0) as outbound
        ")->first()->toArray();

        return [
            'cards' => [
                'total_skus'         => (int) $cards['total_skus'],
                'total_items'        => (int) $cards['total_items'],
                'out_of_stock_count' => (int) $cards['out_of_stock_count'],
                'low_stock_count'    => (int) $cards['low_stock_count'],
                'old_stock_count'    => (int) $cards['old_stock_count'],
            ],
            'stock_movements' => [
                'inbound'  => (int) $movements['inbound'],
                'outbound' => (int) $movements['outbound'],
                'period'   => 'current_month'
            ]
        ];
    }

    public function logChange(array $data): void
    {
        InventoryLog::create($data);
    }
}