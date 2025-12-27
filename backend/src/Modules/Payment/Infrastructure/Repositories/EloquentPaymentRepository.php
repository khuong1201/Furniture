<?php

declare(strict_types=1);

namespace Modules\Payment\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Payment\Domain\Models\Payment;
use Modules\Payment\Domain\Repositories\PaymentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class EloquentPaymentRepository extends EloquentBaseRepository implements PaymentRepositoryInterface 
{
    public function __construct(Payment $model)
    {
        parent::__construct($model);
    }

    public function filter(array $filters): LengthAwarePaginator
    {
        // 1. Khởi tạo query và nạp luôn User để Admin hiển thị tên/email
        $query = $this->model->newQuery()->with(['order.user']);

        // 2. Status Filter - Đảm bảo nếu không truyền status thì lấy sạch (bao gồm cả pending)
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        // 3. Search Filter
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhere('uuid', 'like', "%{$search}%")
                  ->orWhereHas('order', function($oq) use ($search) {
                      $oq->where('code', 'like', "%{$search}%")
                         ->orWhereHas('user', function($uq) use ($search) {
                             $uq->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                         });
                  });
            });
        }

        // 4. Date Range
        if (!empty($filters['date_from'])) {
            try {
                $query->where('created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
            } catch (\Exception $e) {}
        }

        if (!empty($filters['date_to'])) {
            try {
                $query->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
            } catch (\Exception $e) {}
        }

        // 5. Pagination & Sorting
        $perPage = isset($filters['per_page']) ? (int)$filters['per_page'] : 15;

        // Ưu tiên bản ghi mới nhất lên đầu để Admin thấy ngay lập tức
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
    
    // Đảm bảo có hàm này để Service gọi được
    public function updateOrCreate(array $attributes, array $values = []): Payment
    {
        return $this->model->updateOrCreate($attributes, $values);
    }
}