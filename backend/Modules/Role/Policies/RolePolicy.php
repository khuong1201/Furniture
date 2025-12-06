<?php

declare(strict_types=1);

namespace Modules\Role\Policies;

use Modules\User\Domain\Models\User;
use Modules\Role\Domain\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('role.view');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('role.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('role.create');
    }

    public function update(User $user, Role $role): bool
    {
        if ($role->is_system ?? false) {
            return false;
        }

        return $user->hasPermissionTo('role.edit');
    }

    public function delete(User $user, Role $role): bool
    {
        if ($role->is_system ?? false) {
            return false;
        }

        return $user->hasPermissionTo('role.delete');
    }
}
