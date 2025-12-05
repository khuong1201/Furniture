<?php

declare(strict_types=1);

namespace Modules\Product\Policies;

use Modules\User\Domain\Models\User;
use Modules\Product\Domain\Models\Attribute;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttributePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('attribute.view') || $user->hasPermissionTo('product.view');
    }

    public function view(User $user, Attribute $attribute): bool
    {
        return $user->hasPermissionTo('attribute.view') || $user->hasPermissionTo('product.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('attribute.create');
    }

    public function update(User $user, Attribute $attribute): bool
    {
        return $user->hasPermissionTo('attribute.edit');
    }

    public function delete(User $user, Attribute $attribute): bool
    {
        return $user->hasPermissionTo('attribute.delete');
    }
}