<?php

declare(strict_types=1);

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
        // Check thông qua Order relationship nếu payment ko có user_id trực tiếp
        // (Trong migration tôi ko thấy user_id, nên check qua order)
        $ownerId = $payment->order->user_id;
        
        return $user->id === $ownerId || $user->hasPermissionTo('payment.view');
    }

    public function create(User $user): bool
    {
        return true; // Authenticated user can initiate payment
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->hasPermissionTo('payment.edit');
    }
}