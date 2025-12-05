<?php

declare(strict_types=1);

namespace Modules\Notification\Policies;

use Modules\User\Domain\Models\User;
use Modules\Notification\Domain\Models\Notification;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotificationPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Notification $notification): bool
    {
        return $user->id === $notification->user_id;
    }

    public function update(User $user, Notification $notification): bool
    {
        // Update ở đây chủ yếu là mark as read
        return $user->id === $notification->user_id;
    }

    public function delete(User $user, Notification $notification): bool
    {
        return $user->id === $notification->user_id;
    }
}