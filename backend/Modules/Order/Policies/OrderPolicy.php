<?php

namespace Modules\Order\Policies;

use Modules\User\Domain\Models\User;
use Modules\Order\Domain\Models\Order;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('order.view');
    }

    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id || $user->hasPermissionTo('order.view');
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->id === $order->user_id || $user->hasPermissionTo('order.edit');
    }
    
    public function update(User $user, Order $order): bool
    {
        return $user->hasPermissionTo('order.edit');
    }
}