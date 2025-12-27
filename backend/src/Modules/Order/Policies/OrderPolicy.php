<?php

declare(strict_types=1);

namespace Modules\Order\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Order\Domain\Models\Order;
use Modules\User\Domain\Models\User;

class OrderPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return true; 
    }

    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id 
            || $user->hasRole('admin') 
            || $user->hasPermissionTo('order.view_all');
    }

    public function create(User $user): bool
    {
        return $user->is_active; 
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->id === $order->user_id 
            || $user->hasRole('admin')
            || $user->hasPermissionTo('order.edit');
    }
    
    public function update(User $user, Order $order): bool
    {
        return $user->hasRole('admin') 
            || $user->hasPermissionTo('order.edit');
    }
}