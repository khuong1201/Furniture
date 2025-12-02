<?php

namespace Modules\Payment\Policies;

use Modules\User\Domain\Models\User;
use Modules\Payment\Domain\Models\Payment;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true; 
    }

    public function view(User $user, Payment $payment): bool
    {
        $ownerId = $payment->user_id ?? $payment->order->user_id;
        
        return $user->id === $ownerId || $user->hasPermissionTo('payment.view');
    }

    public function create(User $user): bool
    {
        return true; 
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->hasPermissionTo('payment.edit');
    }
}