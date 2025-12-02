<?php

namespace Modules\Product\Policies;

use Modules\User\Domain\Models\User;
use Modules\Product\Domain\Models\Product;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user): bool
    {
        return true; 
    }

    public function view(?User $user, Product $product): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('product.create');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasPermissionTo('product.edit');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->hasPermissionTo('product.delete');
    }
}