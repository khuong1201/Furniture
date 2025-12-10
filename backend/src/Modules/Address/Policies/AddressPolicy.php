<?php

declare(strict_types=1);

namespace Modules\Address\Policies;

use Modules\User\Domain\Models\User;
use Modules\Address\Domain\Models\Address;
use Illuminate\Auth\Access\HandlesAuthorization;

class AddressPolicy 
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool {
        return true; 
    }

    public function view(User $user, Address $address): bool {
        return $user->id === $address->user_id || $user->hasPermissionTo('address.view');
    }

    public function update(User $user, Address $address): bool {
        return $user->id === $address->user_id || $user->hasPermissionTo('address.edit');
    }

    public function delete(User $user, Address $address): bool {
        return $user->id === $address->user_id || $user->hasPermissionTo('address.delete');
    }
}