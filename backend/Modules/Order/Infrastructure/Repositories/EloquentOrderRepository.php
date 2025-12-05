<?php

declare(strict_types=1);

namespace Modules\Order\Infrastructure\Repositories;

use Modules\Order\Domain\Models\Order;
use Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use Modules\Shared\Repositories\EloquentBaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentOrderRepository extends EloquentBaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function countByStatus(): array
    {
        return $this->model
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    public function filter(array $filters): LengthAwarePaginator
    {
        $query = $this->query()->with([
            'items.variant.product', 
            'user'
        ]);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }
}