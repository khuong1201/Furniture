<?php

namespace Modules\Brand\Policies;

use Modules\User\Domain\Models\User;
use Modules\Brand\Domain\Models\Brand;
use Illuminate\Auth\Access\HandlesAuthorization;

class BrandPolicy
{
    use HandlesAuthorization;

    // Admin full quyền, Public chỉ view
    
    public function viewAny(?User $user): bool
    {
        return true; // Ai cũng xem được list (Public API lọc is_active=true)
    }

    public function view(?User $user, Brand $brand): bool
    {
        return true; 
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('brand.create');
    }

    public function update(User $user, Brand $brand): bool
    {
        return $user->hasPermissionTo('brand.edit');
    }

    public function delete(User $user, Brand $brand): bool
    {
        return $user->hasPermissionTo('brand.delete');
    }

}