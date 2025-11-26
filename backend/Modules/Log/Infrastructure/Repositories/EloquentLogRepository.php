<?php
namespace Modules\Log\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Log\Domain\Models\Log;
use Modules\Log\Domain\Repositories\LogRepositoryInterface;

class EloquentLogRepository extends EloquentBaseRepository implements LogRepositoryInterface
{
    public function __construct(Log $model)
    {
        parent::__construct($model);
    }

    public function filter(array $filters)
    {
        $query = $this->query();

        if (!empty($filters['type'])) $query->where('type', $filters['type']);
        if (!empty($filters['action'])) $query->where('action', $filters['action']);
        if (!empty($filters['user_id'])) $query->where('user_id', $filters['user_id']);
        if (!empty($filters['model'])) $query->where('model', $filters['model']);

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }
}
