<?php
namespace Modules\Warehouse\Policies;
use Modules\User\Domain\Models\User;
use Modules\Warehouse\Domain\Models\Warehouse;
use Illuminate\Auth\Access\HandlesAuthorization;

class WarehousePolicy {
    use HandlesAuthorization;

    public function viewAny(User $user): bool {
        return $user->hasPermissionTo('warehouse.view');
    }
    public function view(User $user, Warehouse $warehouse): bool {
        return $user->hasPermissionTo('warehouse.view');
    }
    public function create(User $user): bool {
        return $user->hasPermissionTo('warehouse.create');
    }
    public function update(User $user, Warehouse $warehouse): bool {
        return $user->hasPermissionTo('warehouse.edit');
    }
    public function delete(User $user, Warehouse $warehouse): bool {
        return $user->hasPermissionTo('warehouse.delete');
    }
}