<?php

namespace Modules\Category\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Category\Domain\Repositories\CategoryRepositoryInterface;
use Modules\Category\Domain\Models\Category;

class EloquentCategoryRepository extends EloquentBaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }
    
    public function getTree()
    {
        return $this->model
            ->whereNull('parent_id')
            ->with('allChildren')
            ->get();
    }
    
    public function filter(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->query();
        
        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }
}