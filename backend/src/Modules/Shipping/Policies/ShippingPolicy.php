<?php

declare(strict_types=1);

namespace Modules\Shipping\Policies;

use Modules\User\Domain\Models\User;
use Modules\Shipping\Domain\Models\Shipping;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShippingPolicy 
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool {
        return $user->hasPermissionTo('shipping.view');
    }

    public function view(User $user, Shipping $shipping): bool {
        // Chủ đơn hàng hoặc Admin/Shipper
        return $user->id === $shipping->order->user_id || $user->hasPermissionTo('shipping.view');
    }

    public function create(User $user): bool {
        return $user->hasPermissionTo('shipping.create');
    }

    public function update(User $user, Shipping $shipping): bool {
        return $user->hasPermissionTo('shipping.edit');
    }
    
    public function delete(User $user, Shipping $shipping): bool {
        return $user->hasPermissionTo('shipping.delete');
    }
}