<?php
namespace Modules\Inventory\Policies;
use Modules\User\Domain\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InventoryPolicy {
    use HandlesAuthorization;

    public function viewAny(User $user): bool {
        return $user->hasPermissionTo('inventory.view');
    }

    public function view(User $user): bool {
        return $user->hasPermissionTo('inventory.view');
    }
    
    public function adjust(User $user): bool {
        return $user->hasPermissionTo('inventory.adjust');
    }
}