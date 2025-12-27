<?php

declare(strict_types=1);

namespace Modules\Notification\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Services\BaseService;
use Modules\Shared\Contracts\NotificationServiceInterface;
use Modules\Notification\Domain\Repositories\NotificationRepositoryInterface;
use Modules\Notification\Events\NotificationCreated;

class NotificationService extends BaseService implements NotificationServiceInterface
{
    public function __construct(
        NotificationRepositoryInterface $repository
    ) {
        parent::__construct($repository);
    }

    public function send(int|string $userId, string $title, string $content, string $type = 'info', array $data = []): void
    {
        $notification = $this->repository->create([
            'user_id' => $userId,
            'title'   => $title,
            'content' => $content,
            'type'    => $type,
            'data'    => $data,
            'read_at' => null
        ]);

        try {
            broadcast(new NotificationCreated($notification));
        } catch (Throwable $e) {
            Log::error("Realtime Notification Failed: " . $e->getMessage(), ['code' => 500120]);
        }
    }

    public function getNotificationsWithUnreadCount(int $userId, int $perPage = 15): array
    {
        return [
            'paginator'    => $this->repository->getUserNotifications($userId, $perPage),
            'unread_count' => $this->repository->getUnreadCount($userId),
        ];
    }

    public function markAsRead(string $uuid): void
    {
        $notification = $this->findByUuidOrFail($uuid);
        
        if (is_null($notification->read_at)) {
            $this->repository->update($notification, ['read_at' => now()]);
        }
    }

    public function markAllAsRead(int $userId): void
    {
        $this->repository->markAllAsRead($userId);
    }
}