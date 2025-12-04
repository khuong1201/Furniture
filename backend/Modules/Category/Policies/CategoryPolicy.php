<?php
namespace Modules\Category\Policies;
use Modules\User\Domain\Models\User;
use Modules\Category\Domain\Models\Category;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user): bool
    {
        return true;
    }
    public function view(?User $user, Category $category): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('category.create');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasPermissionTo('category.edit');
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasPermissionTo('category.delete');
    }
}