<?php

declare(strict_types=1);

namespace Modules\Shipping\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Shipping\Domain\Repositories\ShippingRepositoryInterface;
use Modules\Shipping\Domain\Models\Shipping;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentShippingRepository extends EloquentBaseRepository implements ShippingRepositoryInterface
{
    public function __construct(Shipping $model)
    {
        parent::__construct($model);
    }

    public function filter(array $filters): LengthAwarePaginator
    {
        $query = $this->query()->with('order.user'); // Eager load User của Order

        if (!empty($filters['tracking_number'])) {
            $query->where('tracking_number', 'like', "%{$filters['tracking_number']}%");
        }
        
        if (!empty($filters['order_uuid'])) {
            $query->whereHas('order', fn($q) => $q->where('uuid', $filters['order_uuid']));
        }
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }
}