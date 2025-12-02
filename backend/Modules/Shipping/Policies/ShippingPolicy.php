<?php

namespace Modules\Shipping\Policies;

use Modules\User\Domain\Models\User;
use Modules\Shipping\Domain\Models\Shipping;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShippingPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Shipping $shipping): bool
    {
        return $user->hasRole('admin') || $user->id === $shipping->order->user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Shipping $shipping): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Shipping $shipping): bool
    {
        return $user->hasRole('admin');
    }
}