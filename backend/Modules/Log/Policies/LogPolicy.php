<?php

namespace Modules\Log\Policies;

use Modules\User\Domain\Models\User;
use Modules\Log\Domain\Models\Log;
use Illuminate\Auth\Access\HandlesAuthorization;

class LogPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('log.view');
    }

    public function view(User $user, Log $log): bool
    {
        return $user->hasPermissionTo('log.view');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Log $log): bool
    {
        return false;
    }

    public function delete(User $user, Log $log): bool
    {
        return false;
    }
}