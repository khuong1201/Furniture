<?php

namespace Modules\Role\Infrastructure\Repositories;

use Modules\Role\Domain\Repositories\RoleRepositoryInterface;
use Modules\Role\Domain\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentRoleRepository implements RoleRepositoryInterface
{
    public function findById(int|string $id): ?Role
    {
        return Role::find($id);
    }

    public function findByName(string $name): ?Role
    {
        return Role::where('name', $name)->first();
    }

    public function create(array $data): Role
    {
        return Role::create($data);
    }

    public function update(Role $role, array $data): Role
    {
        $role->update($data);
        return $role;
    }

    public function delete(Role $role): bool
    {
        return (bool) $role->delete();
    }

    public function all(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $q = Role::query();
        if (!empty($filters['q'])) {
            $q->where('name', 'like', '%'.$filters['q'].'%')
              ->orWhere('description', 'like', '%'.$filters['q'].'%');
        }
        return $q->orderBy('id','desc')->paginate($perPage);
    }
}
