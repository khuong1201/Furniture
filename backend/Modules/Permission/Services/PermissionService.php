<?php

namespace Modules\Permission\Services;

use Modules\Permission\Domain\Repositories\PermissionRepositoryInterface;
use Modules\Permission\Domain\Models\Permission;
use Modules\Shared\Services\BaseService;

class PermissionService extends BaseService
{
    public function __construct(PermissionRepositoryInterface $repo)
    {
        parent::__construct($repo);
    }

    public function getPermissionsByUserId(int $userId): array
    {
        $permissions = $this->repository->getPermissionsByUserId($userId);
        return array_values(array_unique(array_map('strtolower', $permissions)));
    }

    public function userHasPermission(int $userId, string $permission): bool
    {
        return in_array(strtolower($permission), $this->getPermissionsByUserId($userId));
    }

    public function findByName(string $name): ?Permission
    {
        return $this->repository->findByName($name);
    }
}