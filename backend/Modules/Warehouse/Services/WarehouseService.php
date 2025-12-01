<?php

namespace Modules\Warehouse\Services;

use Modules\Shared\Services\BaseService;
use Modules\Warehouse\Domain\Repositories\WarehouseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class WarehouseService extends BaseService
{
    public function __construct(WarehouseRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
    
    public function paginate(int $perPage = 15, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $filters['per_page'] = $perPage;
        if (method_exists($this->repository, 'filter')) {
            return $this->repository->filter($filters);
        }
        return $this->repository->paginate($perPage);
    }

    protected function beforeDelete(Model $model): void
    {
        $hasStock = $model->products()->wherePivot('quantity', '>', 0)->exists();

        if ($hasStock) {
            throw ValidationException::withMessages([
                'warehouse' => ['Cannot delete warehouse containing stock. Please transfer or clear stock first.']
            ]);
        }
    }
}