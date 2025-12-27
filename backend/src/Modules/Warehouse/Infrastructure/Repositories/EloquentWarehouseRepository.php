<?php

declare(strict_types=1);

namespace Modules\Warehouse\Infrastructure\Repositories;

use Carbon\Carbon;
use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Warehouse\Domain\Repositories\WarehouseRepositoryInterface;
use Modules\Warehouse\Domain\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentWarehouseRepository extends EloquentBaseRepository implements WarehouseRepositoryInterface
{
    public function __construct(Warehouse $model)
    {
        parent::__construct($model);
    }

    public function findByName(string $name): ?Warehouse
    {
        return $this->model->where('name', $name)->first();
    }

    public function hasStock(int $warehouseId): bool
    {
        return $this->model->find($warehouseId)
            ->stocks()
            ->where('quantity', '>', 0)
            ->exists();
    }

    public function filter(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with('manager'); 

        if (!empty($filters['search'])) {
            $q = $filters['search'];
            $query->where(function(Builder $sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('location', 'like', "%{$q}%");
            });
        }
        
        if (!empty($filters['manager_id'])) {
            $query->where('manager_id', $filters['manager_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function getDashboardStats(string $uuid): array
    {
        // Requirement: Old stock is defined as items not updated in 6 months
        $oldStockDate = Carbon::now()->subMonths(6);

        $warehouse = $this->model->where('uuid', $uuid)->first();

        if (!$warehouse) {
            return [
                'total_skus'         => 0, 
                'total_items'        => 0, 
                'out_of_stock_count' => 0,
                'low_stock_count'    => 0, 
                'old_stock_count'    => 0
            ];
        }

        $stats = $warehouse->stocks()
            ->selectRaw('
                COUNT(*) as total_skus,
                SUM(quantity) as total_items,
                SUM(CASE WHEN quantity = 0 THEN 1 ELSE 0 END) as out_of_stock_count,
                
                -- FIX: So sánh với min_threshold của từng dòng thay vì số 10 cứng
                SUM(CASE WHEN quantity > 0 AND quantity <= COALESCE(min_threshold, 10) THEN 1 ELSE 0 END) as low_stock_count,
                
                SUM(CASE WHEN updated_at <= ? THEN 1 ELSE 0 END) as old_stock_count
            ', [$oldStockDate])
            ->first();

        return [
            'total_skus'         => (int) ($stats->total_skus ?? 0),
            'total_items'        => (int) ($stats->total_items ?? 0),
            'out_of_stock_count' => (int) ($stats->out_of_stock_count ?? 0),
            'low_stock_count'    => (int) ($stats->low_stock_count ?? 0),
            'old_stock_count'    => (int) ($stats->old_stock_count ?? 0),
        ];
    }
}