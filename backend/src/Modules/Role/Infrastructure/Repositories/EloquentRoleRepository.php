<?php

declare(strict_types=1);

namespace Modules\Role\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Role\Domain\Repositories\RoleRepositoryInterface;
use Modules\Role\Domain\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentRoleRepository extends EloquentBaseRepository implements RoleRepositoryInterface
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    public function findByName(string $name): ?Role
    {
        return $this->model->where('name', $name)->first();
    }

    public function findBySlug(string $slug): ?Role
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query();

        if (!empty($filters['q'])) {
            $query->where('name', 'like', "%{$filters['q']}%")
                  ->orWhere('description', 'like', "%{$filters['q']}%");
        }

        return $query->orderBy('priority', 'desc')
                     ->orderBy('id', 'asc')
                     ->paginate($perPage);
    }
}