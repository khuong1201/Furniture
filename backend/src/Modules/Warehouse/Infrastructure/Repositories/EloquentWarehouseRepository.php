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

    public function paginate($perPage = 15)
    {
        $query = $this->model->newQuery();

        if (!empty(request('search'))) {
            $query->where('name', 'like', '%' . request('search') . '%');
        }

        return $query->latest()->paginate($perPage);
    }
}
