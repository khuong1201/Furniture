<?php

declare(strict_types=1);

namespace Modules\User\Policies;

use Modules\User\Domain\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy {
    use HandlesAuthorization;

    public function viewAny(User $user): bool {
        return $user->hasPermissionTo('user.view');
    }

    public function view(User $user, User $model): bool {
        return $user->id === $model->id || $user->hasPermissionTo('user.view');
    }

    public function create(User $user): bool {
        return $user->hasPermissionTo('user.create');
    }

    public function update(User $user, User $model): bool {
        return $user->id === $model->id || $user->hasPermissionTo('user.edit');
    }

    public function delete(User $user, User $model): bool {
        return $user->hasPermissionTo('user.delete') && $user->id !== $model->id;
    }
}