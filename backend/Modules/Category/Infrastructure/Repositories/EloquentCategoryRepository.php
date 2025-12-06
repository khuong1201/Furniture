<?php

declare(strict_types=1);

namespace Modules\Category\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Category\Domain\Repositories\CategoryRepositoryInterface;
use Modules\Category\Domain\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentCategoryRepository extends EloquentBaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function getTree(bool $includeInactive = false): Collection
    {
        $query = $this->model->whereNull('parent_id');

        if (!$includeInactive) {
            $query->where('is_active', true);
        }

        return $query->with([
            'allChildren' => function ($q) use ($includeInactive) {
                if (!$includeInactive) {
                    $q->where('is_active', true);
                }
            }
        ])
            ->get();
    }

    public function filter(array $filters): LengthAwarePaginator
    {
        $query = $this->query();

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }
}