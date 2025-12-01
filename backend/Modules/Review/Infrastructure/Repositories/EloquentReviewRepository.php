<?php

namespace Modules\Review\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Review\Domain\Repositories\ReviewRepositoryInterface;
use Modules\Review\Domain\Models\Review;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentReviewRepository extends EloquentBaseRepository implements ReviewRepositoryInterface
{
    public function __construct(Review $model)
    {
        parent::__construct($model);
    }

    public function filter(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->query()->with(['user:id,name,avatar_url']); 

        if (!empty($filters['product_uuid'])) {
            $query->whereHas('product', function($q) use ($filters) {
                $q->where('uuid', $filters['product_uuid']);
            });
        }

        if (isset($filters['is_approved'])) {
            $query->where('is_approved', (bool)$filters['is_approved']);
        }
        
        if (!empty($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }
}