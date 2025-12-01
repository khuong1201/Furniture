<?php

namespace Modules\Promotion\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Promotion\Domain\Models\Promotion;
use Modules\Promotion\Domain\Repositories\PromotionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentPromotionRepository extends EloquentBaseRepository implements PromotionRepositoryInterface
{
    public function __construct(Promotion $model)
    {
        parent::__construct($model);
    }

    public function filter(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->query();

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (isset($filters['is_active']) && $filters['is_active']) {
            $query->active();
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }
}