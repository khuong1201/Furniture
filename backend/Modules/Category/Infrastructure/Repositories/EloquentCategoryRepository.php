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

    public function getTree(): Collection
    {
        return $this->model
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with(['allChildren' => function($query) {
                $query->where('is_active', true);
            }])
            ->get();
    }

    public function filter(array $filters): LengthAwarePaginator
    {
        $query = $this->query();

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }
        
        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool)$filters['is_active']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }
}