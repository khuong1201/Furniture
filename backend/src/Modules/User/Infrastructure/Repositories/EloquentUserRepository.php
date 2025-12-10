<?php

declare(strict_types=1);

namespace Modules\User\Infrastructure\Repositories;

use Modules\User\Domain\Repositories\UserRepositoryInterface;
use Modules\User\Domain\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Shared\Repositories\EloquentBaseRepository;
use Illuminate\Database\Eloquent\Builder;

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
            $query->where(function (Builder $sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "{$q}%"); 
            });
        }
        
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($filters['role_id'])) {
            $query->whereHas('roles', fn($q) => $q->where('id', $filters['role_id']));
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }
}