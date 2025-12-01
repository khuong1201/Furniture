<?php

namespace Modules\Shipping\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Shipping\Domain\Repositories\ShippingRepositoryInterface;
use Modules\Shipping\Domain\Models\Shipping;

class EloquentShippingRepository extends EloquentBaseRepository implements ShippingRepositoryInterface
{
    public function __construct(Shipping $model)
    {
        parent::__construct($model);
    }

    public function filter(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->query()->with('order');

        if (!empty($filters['tracking_number'])) {
            $query->where('tracking_number', 'like', "%{$filters['tracking_number']}%");
        }
        
        if (!empty($filters['order_uuid'])) {
            $query->whereHas('order', fn($q) => $q->where('uuid', $filters['order_uuid']));
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }
}