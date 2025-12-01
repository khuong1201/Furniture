<?php

namespace Modules\Role\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Role\Domain\Repositories\RoleRepositoryInterface;
use Modules\Role\Domain\Models\Role;

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
}