<?php

declare(strict_types=1);

namespace Modules\Voucher\Policies;

use Modules\User\Domain\Models\User;
use Modules\Voucher\Domain\Models\Voucher;
use Illuminate\Auth\Access\HandlesAuthorization;

class VoucherPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool {
        return $user->hasPermissionTo('voucher.view');
    }
    public function view(User $user, Voucher $voucher): bool {
        return $user->hasPermissionTo('voucher.view');
    }
    public function create(User $user): bool {
        return $user->hasPermissionTo('voucher.create');
    }
    public function update(User $user, Voucher $voucher): bool {
        return $user->hasPermissionTo('voucher.edit');
    }
    public function delete(User $user, Voucher $voucher): bool {
        return $user->hasPermissionTo('voucher.delete');
    }
}