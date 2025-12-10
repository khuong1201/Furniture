<?php

declare(strict_types=1);

namespace Modules\Inventory\Policies;

use Modules\User\Domain\Models\User;
use Modules\Inventory\Domain\Models\InventoryStock;
use Illuminate\Auth\Access\HandlesAuthorization;

class InventoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('inventory.view');
    }

    public function view(User $user, InventoryStock $stock): bool
    {
        return $user->hasPermissionTo('inventory.view');
    }

    public function create(User $user): bool 
    {
        return $user->hasPermissionTo('inventory.edit'); 
    }
}