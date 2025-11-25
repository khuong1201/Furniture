<?php

namespace Modules\Permission\Domain\Repositories;

use Illuminate\Support\Collection;
use Modules\Permission\Domain\Models\Permission;

interface PermissionRepositoryInterface
{
    public function getPermissionsByUserId(int $userId): array;

    public function findByName(string $name): ?Permission;

    public function create(array $data): Permission;

    public function all(): Collection;
}
