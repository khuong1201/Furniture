<?php

declare(strict_types=1);

namespace Modules\Permission\Infrastructure\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\Permission\Domain\Models\Permission;
use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Permission\Domain\Repositories\PermissionRepositoryInterface;

class EloquentPermissionRepository extends EloquentBaseRepository implements PermissionRepositoryInterface
{
    public function __construct(Permission $model)
    {
        parent::__construct($model);
    }

    public function getPermissionsByUserId(int $userId): array
    {
        return DB::table('permissions')
            ->join('permission_role', 'permissions.id', '=', 'permission_role.permission_id')
            ->join('role_user', 'permission_role.role_id', '=', 'role_user.role_id')
            ->where('role_user.user_id', $userId)
            ->distinct()
            ->pluck('permissions.name')
            ->toArray();
    }

    public function findByName(string $name): ?Permission
    {
        return $this->model->where('name', $name)->first();
    }
}