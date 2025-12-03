<?php

namespace Modules\Collection\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Collection\Domain\Repositories\CollectionRepositoryInterface;
use Modules\Collection\Domain\Models\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentCollectionRepository extends EloquentBaseRepository implements CollectionRepositoryInterface
{
    public function __construct(Collection $model) {
        parent::__construct($model);
    }

    public function filter(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool)$filters['is_active']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }
}