<?php

declare(strict_types=1);

namespace Modules\Wishlist\Policies;

use Modules\User\Domain\Models\User;
use Modules\Wishlist\Domain\Models\Wishlist;
use Illuminate\Auth\Access\HandlesAuthorization;

class WishlistPolicy
{
    use HandlesAuthorization;

    // Mọi user login đều xem được ds của mình
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function delete(User $user, Wishlist $wishlist): bool
    {
        return $user->id === $wishlist->user_id;
    }
}