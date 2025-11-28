<?php

namespace Modules\Permission\Domain\Repositories;

use Illuminate\Support\Collection;
use Modules\Permission\Domain\Models\Permission;
use Modules\Shared\Repositories\BaseRepositoryInterface;
interface PermissionRepositoryInterface extends BaseRepositoryInterface
{
    public function getPermissionsByUserId(int $userId): array;

    public function findByName(string $name): ?Permission;
}
