<?php

declare(strict_types=1);

namespace Modules\Notification\Domain\Repositories;

use Modules\Shared\Repositories\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationRepositoryInterface extends BaseRepositoryInterface
{
    public function getUserNotifications(int $userId, int $perPage = 15): LengthAwarePaginator;
    public function getUnreadCount(int $userId): int;
    public function markAllAsRead(int $userId): void;
}