<?php

namespace Modules\Product\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Product\Domain\Models\Product;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;

class EloquentProductRepository extends EloquentBaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function filter(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->query()->with(['category', 'images'])->withAvg('reviews', 'rating'); ;

        if (!empty($filters['search'])) {
            $q = $filters['search'];
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%");
            });
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        
        if (isset($filters['status'])) {
            $query->where('status', (bool)$filters['status']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }
}