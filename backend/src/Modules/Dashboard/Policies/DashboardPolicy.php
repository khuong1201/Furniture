<?php

declare(strict_types=1);

namespace Modules\Dashboard\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\User\Domain\Models\User;

class DashboardPolicy
{
    use HandlesAuthorization;

    /**
     * View dashboard summary (Admin only)
     */
    public function view(User $user): bool
    {
        return $user->is_active
            && $user->hasPermissionTo('dashboard.view');
    }
}
