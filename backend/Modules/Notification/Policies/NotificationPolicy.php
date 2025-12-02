<?php
namespace Modules\Notification\Policies;
use Modules\User\Domain\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotificationPolicy {
    use HandlesAuthorization;

    public function view(User $user, DatabaseNotification $notification): bool {
        return $user->id === (int) $notification->notifiable_id;
    }

    public function update(User $user, DatabaseNotification $notification): bool {
        return $user->id === (int) $notification->notifiable_id;
    }

    public function delete(User $user, DatabaseNotification $notification): bool {
        return $user->id === (int) $notification->notifiable_id;
    }
}