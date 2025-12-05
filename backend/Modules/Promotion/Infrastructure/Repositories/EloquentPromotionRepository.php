<?php

declare(strict_types=1);

namespace Modules\Promotion\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Promotion\Domain\Models\Promotion;
use Modules\Promotion\Domain\Repositories\PromotionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentPromotionRepository extends EloquentBaseRepository implements PromotionRepositoryInterface
{
    public function __construct(Promotion $model)
    {
        parent::__construct($model);
    }

    public function filter(array $filters): LengthAwarePaginator
    {
        $query = $this->query();

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        // Cast boolean filters correctly
        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }
        
        // Filter chỉ lấy những khuyến mãi đang có hiệu lực (Active + Date Range)
        if (!empty($filters['valid_now']) && filter_var($filters['valid_now'], FILTER_VALIDATE_BOOLEAN)) {
            $query->active();
        }

        return $query->latest()->paginate((int) ($filters['per_page'] ?? 15));
    }
}