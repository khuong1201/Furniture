<?php

declare(strict_types=1);

namespace Modules\Permission\Policies;

use Modules\User\Domain\Models\User;
use Modules\Permission\Domain\Models\Permission;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermissionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('permission.view');
    }

    public function view(User $user, Permission $permission): bool
    {
        return $user->hasPermissionTo('permission.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('permission.create');
    }

    public function update(User $user, Permission $permission): bool
    {
        return $user->hasPermissionTo('permission.edit');
    }

    public function delete(User $user, Permission $permission): bool
    {
        if ($permission->is_system ?? false) {
            return false;
        }

        return $user->hasPermissionTo('permission.delete');
    }
}
