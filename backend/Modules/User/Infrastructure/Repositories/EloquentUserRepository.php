<?php

namespace Modules\User\Infrastructure\Repositories;

use Modules\User\Domain\Repositories\UserRepositoryInterface;
use Modules\User\Domain\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Shared\Repositories\EloquentBaseRepository;

class EloquentUserRepository extends EloquentBaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function filter(array $filters): LengthAwarePaginator
    {
        $query = $this->query();

        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }
        
        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool)$filters['is_active']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }
}
