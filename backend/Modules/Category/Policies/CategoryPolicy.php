<?php

declare(strict_types=1);

namespace Modules\Category\Policies;

use Modules\User\Domain\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user): bool { return true; }
    public function view(?User $user): bool { return true; }
    
    public function create(User $user): bool 
    { 
        return $user->hasPermissionTo('category.create'); 
    }
    
    public function update(User $user): bool 
    { 
        return $user->hasPermissionTo('category.edit'); 
    }
    
    public function delete(User $user): bool 
    { 
        return $user->hasPermissionTo('category.delete'); 
    }
}