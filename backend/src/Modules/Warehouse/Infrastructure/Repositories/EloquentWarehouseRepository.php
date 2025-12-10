<?php

declare(strict_types=1);

namespace Modules\Warehouse\Infrastructure\Repositories;

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

    public function filter(array $filters): LengthAwarePaginator
    {
        $query = $this->query()->with('manager'); 

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
            $query->where('is_active', (bool)$filters['is_active']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function hasStock(int $warehouseId): bool
    {
        // Check relation stocks (Inventory Module)
        return $this->model->find($warehouseId)
            ->stocks()
            ->where('quantity', '>', 0)
            ->exists();
    }
}