<?php

namespace Modules\Warehouse\Infrastructure\Repositories;

use Modules\Warehouse\Domain\Models\Warehouse;
use Modules\Warehouse\Domain\Repositories\WarehouseRepositoryInterface;
use Modules\Shared\Repositories\EloquentBaseRepository;

class EloquentWarehouseRepository extends EloquentBaseRepository implements WarehouseRepositoryInterface
{
    public function __construct(Warehouse $model)
    {
        parent::__construct($model);
    }

    public function filter(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->query()->with('manager'); 

        if (!empty($filters['search'])) {
            $q = $filters['search'];
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('location', 'like', "%{$q}%");
            });
        }
        
        if (!empty($filters['manager_id'])) {
            $query->where('manager_id', $filters['manager_id']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }
}
