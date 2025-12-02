<?php

namespace Modules\User\Policies;

use Modules\User\Domain\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->hasRole('admin');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasRole('admin') && $user->id !== $model->id;
    }
}