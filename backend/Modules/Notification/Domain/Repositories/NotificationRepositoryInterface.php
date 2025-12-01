<?php

namespace Modules\Notification\Domain\Repositories;

use Modules\Shared\Repositories\BaseRepositoryInterface;
use Modules\Notification\Domain\Models\Notification;

interface NotificationRepositoryInterface extends BaseRepositoryInterface
{
    public function getUserNotifications(int $userId, int $perPage = 15);
    public function getUnreadCount(int $userId): int;
    public function markAllAsRead(int $userId): void;
}