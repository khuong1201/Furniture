<?php

declare(strict_types=1);

namespace Modules\Order\Policies;

use Modules\User\Domain\Models\User;
use Modules\Order\Domain\Models\Order;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true; 
    }

    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id || $user->hasPermissionTo('order.view_all');
    }

    public function create(User $user): bool
    {
        return $user->is_active ?? true; 
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