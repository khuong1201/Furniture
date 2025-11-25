<?php

namespace Modules\Role\Services;

use Modules\Role\Domain\Repositories\RoleRepositoryInterface;
use Modules\User\Domain\Models\User;
use Modules\Role\Events\RoleAssigned;
use Illuminate\Support\Facades\DB;

class RoleService
{
    public function __construct(protected RoleRepositoryInterface $repo) {}

    public function assignRoleToUser(User $user, string $roleName): void
    {
        $role = $this->repo->findByName($roleName);
        if (! $role) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Role not found: {$roleName}");
        }

        DB::transaction(function () use ($user, $role) {
            $user->roles()->syncWithoutDetaching([$role->id]);
            event(new RoleAssigned($user));
        });
    }

    public function removeRoleFromUser(User $user, string $roleName): void
    {
        $role = $this->repo->findByName($roleName);
        if (! $role) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Role not found: {$roleName}");
        }

        DB::transaction(function () use ($user, $role) {
            $user->roles()->detach($role->id);
            event(new RoleAssigned($user));
        });
    }

    public function createRole(array $data)
    {
        return $this->repo->create($data);
    }

    public function updateRole($id, array $data)
    {
        $role = $this->repo->findById($id);
        if (! $role) throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
        return $this->repo->update($role, $data);
    }

    public function deleteRole($id)
    {
        $role = $this->repo->findById($id);
        if (! $role) throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
        return $this->repo->delete($role);
    }

    public function listRoles(int $perPage = 15, array $filters = [])
    {
        return $this->repo->all($perPage, $filters);
    }
}
