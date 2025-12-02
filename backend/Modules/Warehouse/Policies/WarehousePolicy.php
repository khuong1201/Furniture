<?php

namespace Modules\Warehouse\Policies;

use Modules\User\Domain\Models\User;
use Modules\Warehouse\Domain\Models\Warehouse;
use Illuminate\Auth\Access\HandlesAuthorization;

class WarehousePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, Warehouse $warehouse): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Warehouse $warehouse): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Warehouse $warehouse): bool
    {
        return $user->hasRole('admin');
    }
}