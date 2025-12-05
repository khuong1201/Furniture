<?php

declare(strict_types=1);

namespace Modules\Cart\Policies;

use Modules\User\Domain\Models\User;
use Modules\Cart\Domain\Models\Cart;
use Modules\Cart\Domain\Models\CartItem;
use Illuminate\Auth\Access\HandlesAuthorization;

class CartPolicy
{
    use HandlesAuthorization;
    
    public function clear(User $user, Cart $cart): bool
    {
        return $user->id === $cart->user_id;
    }

    public function update(User $user, CartItem $cartItem): bool
    {
        return $user->id === $cartItem->cart->user_id;
    }

    public function delete(User $user, CartItem $cartItem): bool
    {
        return $user->id === $cartItem->cart->user_id;
    }
}