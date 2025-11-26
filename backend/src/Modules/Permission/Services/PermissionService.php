<?php

namespace Modules\Permission\Services;

use Modules\Permission\Domain\Repositories\PermissionRepositoryInterface;
use Modules\Permission\Domain\Models\Permission;

class PermissionService
{
    public function __construct(private PermissionRepositoryInterface $repo) {}

    public function getPermissionsByUserId(int $userId): array
    {
        $permissions = $this->repo->getPermissionsByUserId($userId);
        $normalized = array_map('strtolower', $permissions);
        return array_values(array_unique($normalized));
    }

    public function findByName(string $name): ?Permission
    {
        return $this->repo->findByName($name);
    }

    public function create(array $data): Permission
    {
        return $this->repo->create($data);
    }

    public function all()
    {
        return $this->repo->all();
    }
}
