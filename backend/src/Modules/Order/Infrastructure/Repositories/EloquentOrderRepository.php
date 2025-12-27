<?php

declare(strict_types=1);

namespace Modules\Order\Infrastructure\Repositories;

use Modules\Order\Domain\Models\Order;
use Modules\Order\Domain\Repositories\OrderRepositoryInterface;
use Modules\Shared\Repositories\EloquentBaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class EloquentOrderRepository extends EloquentBaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function findForPayment(string $uuid): ?Order
    {
        return $this->model->where('uuid', $uuid)
            ->lockForUpdate() // Quan trọng: Khóa dòng để tránh race condition khi thanh toán
            ->first();
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
        $query = $this->model->query()->with(['items', 'user']);

        // 1. Status Filter
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }
        
        // 2. User Filter
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // 3. Search (Code, UUID, Tên khách, Email khách)
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function($q) use ($search) {
                $q->where('uuid', 'like', $search)
                  ->orWhere('code', 'like', $search)
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', $search)
                         ->orWhere('email', 'like', $search);
                  });
            });
        }

        // 4. Date Range Filter
        if (!empty($filters['date_from'])) {
            try {
                $dateFrom = Carbon::parse($filters['date_from'])->startOfDay();
                $query->where('created_at', '>=', $dateFrom);
            } catch (\Exception $e) {}
        }

        if (!empty($filters['date_to'])) {
            try {
                $dateTo = Carbon::parse($filters['date_to'])->endOfDay();
                $query->where('created_at', '<=', $dateTo);
            } catch (\Exception $e) {}
        }

        // 5. Sort
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $sortBy = $filters['sort_by'] ?? 'created_at';
        
        if (in_array($sortBy, ['created_at', 'grand_total', 'updated_at'])) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->latest();
        }
        $perPage = isset($filters['per_page']) ? (int)$filters['per_page'] : 15;

        return $query->paginate($perPage);
    }
}