<?php

declare(strict_types=1);

namespace Modules\Log\Infrastructure\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Log\Domain\Models\Log;
use Modules\Log\Domain\Repositories\LogRepositoryInterface;
use Modules\Shared\Repositories\EloquentBaseRepository;

class EloquentLogRepository extends EloquentBaseRepository implements LogRepositoryInterface
{
    public function __construct(Log $model)
    {
        parent::__construct($model);
    }

    public function getLogsByFilters(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->query()
            ->with('user:id,name,email') 
            ->when($filters['type'] ?? null, fn ($q, $v) => $q->where('type', $v))
            ->when($filters['action'] ?? null, fn ($q, $v) => $q->where('action', $v))
            ->when($filters['user_id'] ?? null, fn ($q, $v) => $q->where('user_id', $v))
            ->when($filters['model'] ?? null, fn ($q, $v) => $q->where('model', 'like', "%$v%"))
            ->when($filters['model_uuid'] ?? null, fn ($q, $v) => $q->where('model_uuid', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest()
            ->paginate($perPage);
    }
}