<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Product\Domain\Models\Attribute;
use Modules\Product\Domain\Repositories\AttributeRepositoryInterface;
use Modules\Shared\Repositories\EloquentBaseRepository;

class EloquentAttributeRepository extends EloquentBaseRepository implements AttributeRepositoryInterface 
{
    public function __construct(Attribute $model) 
    { 
        parent::__construct($model); 
    }

    public function filter(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // 1. Eager Loading
        if (!empty($filters['with'])) {
            $query->with($filters['with']);
        }

        // 2. Search Logic
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // 3. Filter by Type
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // 4. Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        
        if (in_array($sortBy, ['id', 'name', 'created_at', 'sort_order'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }

        return $query->paginate((int) ($filters['per_page'] ?? 15));
    }
}