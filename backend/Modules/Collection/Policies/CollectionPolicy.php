<?php

namespace Modules\Collection\Policies;

use Modules\User\Domain\Models\User;
use Modules\Collection\Domain\Models\Collection;
use Illuminate\Auth\Access\HandlesAuthorization;

class CollectionPolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user): bool { return true; } 
    public function view(?User $user, Collection $collection): bool { return true; } 

    public function create(User $user): bool {
        return $user->hasPermissionTo('collection.create');
    }

    public function update(User $user, Collection $collection): bool {
        return $user->hasPermissionTo('collection.edit');
    }

    public function delete(User $user, Collection $collection): bool {
        return $user->hasPermissionTo('collection.delete');
    }
}