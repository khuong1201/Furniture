<?php

namespace Modules\Address\Policies;

use Modules\User\Domain\Models\User;
use Modules\Address\Domain\Models\Address;
use Illuminate\Auth\Access\HandlesAuthorization;

class AddressPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Address $address): bool
    {
        return $user->id === $address->user_id || $user->hasRole('admin');
    }

    public function update(User $user, Address $address): bool
    {
        return $user->id === $address->user_id;
    }

    public function delete(User $user, Address $address): bool
    {
        return $user->id === $address->user_id || $user->hasRole('admin');
    }
}