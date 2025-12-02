<?php

namespace Modules\Payment\Policies;

use Modules\User\Domain\Models\User;
use Modules\Payment\Domain\Models\Payment;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Payment $payment): bool
    {
        return $user->hasRole('admin') || $user->id === $payment->order->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->hasRole('admin');
    }
}