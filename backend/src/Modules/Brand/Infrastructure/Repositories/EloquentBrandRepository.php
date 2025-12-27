<?php

declare(strict_types=1);

namespace Modules\Brand\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Brand\Domain\Repositories\BrandRepositoryInterface;
use Modules\Brand\Domain\Models\Brand;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentBrandRepository extends EloquentBaseRepository implements BrandRepositoryInterface
{
    public function __construct(Brand $model)
    {
        parent::__construct($model);
    }

    public function filter(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // 1. Tìm kiếm
        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        // 2. Trạng thái
        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        // 3. Sort mặc định
        $query->orderBy('sort_order')->orderByDesc('created_at');

        return $query->paginate((int) ($filters['per_page'] ?? 15));
    }
}