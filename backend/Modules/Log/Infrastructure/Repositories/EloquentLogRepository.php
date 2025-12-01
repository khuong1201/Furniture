<?php
namespace Modules\Log\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Log\Domain\Models\Log;
use Modules\Log\Domain\Repositories\LogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentLogRepository extends EloquentBaseRepository implements LogRepositoryInterface
{
    public function __construct(Log $model)
    {
        parent::__construct($model);
    }

    public function filter(array $filters): LengthAwarePaginator
    {
        $query = $this->query()->with('user:id,name,email'); // Eager load user

        if (!empty($filters['type'])) $query->where('type', $filters['type']);
        if (!empty($filters['action'])) $query->where('action', $filters['action']);
        
        if (!empty($filters['user_id'])) $query->where('user_id', $filters['user_id']);
        
        if (!empty($filters['model'])) $query->where('model', 'like', "%{$filters['model']}%");
        if (!empty($filters['model_uuid'])) $query->where('model_uuid', $filters['model_uuid']);

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 20);
    }
}