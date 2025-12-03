<?php

namespace Modules\Product\Policies;

use Modules\User\Domain\Models\User;
use Modules\Product\Domain\Models\Attribute;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttributePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true; 
    }

    public function view(User $user, Attribute $attribute): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('product.create');
    }

    public function update(User $user, Attribute $attribute): bool
    {
        return $user->hasPermissionTo('product.edit');
    }

    public function delete(User $user, Attribute $attribute): bool
    {
        return $user->hasPermissionTo('product.delete');
    }
}