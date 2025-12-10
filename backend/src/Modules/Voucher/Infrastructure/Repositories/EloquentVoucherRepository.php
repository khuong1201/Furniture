<?php

declare(strict_types=1);

namespace Modules\Voucher\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Voucher\Domain\Repositories\VoucherRepositoryInterface;
use Modules\Voucher\Domain\Models\Voucher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentVoucherRepository extends EloquentBaseRepository implements VoucherRepositoryInterface
{
    public function __construct(Voucher $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code): ?Voucher
    {
        return $this->model->where('code', $code)->first();
    }

    public function filter(array $filters): LengthAwarePaginator
    {
        $query = $this->query();

        if (!empty($filters['search'])) {
            $q = $filters['search'];
            $query->where(function($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool)$filters['is_active']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }
}