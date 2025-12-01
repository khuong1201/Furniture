<?php

namespace Modules\Notification\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Notification\Domain\Models\Notification;
use Modules\Notification\Domain\Repositories\NotificationRepositoryInterface;

class EloquentNotificationRepository extends EloquentBaseRepository implements NotificationRepositoryInterface
{
    public function __construct(Notification $model)
    {
        parent::__construct($model);
    }

    public function getUserNotifications(int $userId, int $perPage = 15)
    {
        return $this->model
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function getUnreadCount(int $userId): int
    {
        return $this->model
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    public function markAllAsRead(int $userId): void
    {
        $this->model
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}