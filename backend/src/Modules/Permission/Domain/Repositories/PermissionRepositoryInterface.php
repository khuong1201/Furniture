<?php

namespace Modules\Permission\Domain\Repositories;

use Modules\Shared\Repositories\BaseRepositoryInterface;
use Modules\Permission\Domain\Models\Permission;
use Illuminate\Support\Facades\DB;

interface PermissionRepositoryInterface extends BaseRepositoryInterface
{
    public function getPermissionsByUserId(int $userId): array;
    public function findByName(string $name): ?Permission;
}