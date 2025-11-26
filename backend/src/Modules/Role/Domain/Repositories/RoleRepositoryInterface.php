<?php

namespace Modules\Role\Domain\Repositories;

use Modules\Role\Domain\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;

interface RoleRepositoryInterface
{
    public function findById(int|string $id): ?Role;
    public function findByName(string $name): ?Role;
    public function create(array $data): Role;
    public function update(Role $role, array $data): Role;
    public function delete(Role $role): bool;
    public function all(int $perPage = 15, array $filters = []): LengthAwarePaginator;
}
