<?php
namespace Modules\Promotion\Policies;
use Modules\User\Domain\Models\User;
use Modules\Promotion\Domain\Models\Promotion;
use Illuminate\Auth\Access\HandlesAuthorization;

class PromotionPolicy {
    use HandlesAuthorization;

    public function viewAny(User $user): bool {
        return $user->hasPermissionTo('promotion.view');
    }
    
    public function view(User $user, Promotion $promotion): bool {
        return $user->hasPermissionTo('promotion.view');
    }

    public function create(User $user): bool {
        return $user->hasPermissionTo('promotion.create');
    }

    public function update(User $user, Promotion $promotion): bool {
        return $user->hasPermissionTo('promotion.edit');
    }

    public function delete(User $user, Promotion $promotion): bool {
        return $user->hasPermissionTo('promotion.delete');
    }
}