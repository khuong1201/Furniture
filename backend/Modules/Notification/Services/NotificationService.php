<?php

declare(strict_types=1);

namespace Modules\Notification\Services;

use Modules\Shared\Services\BaseService;
use Modules\Notification\Domain\Repositories\NotificationRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class NotificationService extends BaseService
{
    public function __construct(NotificationRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getMyNotifications(int $userId, int $perPage = 15): array
    {
        $notifications = $this->repository->getUserNotifications($userId, $perPage);
        $unreadCount = $this->repository->getUnreadCount($userId);
        
        return [
            'items' => $notifications,
            'unread_count' => $unreadCount
        ];
    }

    public function send(int $userId, string $title, string $content, string $type = 'info', array $data = []): Model
    {
        // Lưu vào Database (In-app Notification)
        $notification = $this->repository->create([
            'user_id' => $userId,
            'title' => $title,
            'content' => $content,
            'type' => $type,
            'data' => $data,
            'read_at' => null
        ]);

        // TODO: Tích hợp Pusher/Firebase để bắn realtime tại đây nếu cần
        // broadcast(new NotificationSent($notification));

        return $notification;
    }

    public function markAsRead(string $uuid): void
    {
        $notification = $this->findByUuidOrFail($uuid);
        
        if (is_null($notification->read_at)) {
            $notification->update(['read_at' => now()]);
        }
    }

    public function markAllAsRead(int $userId): void
    {
        $this->repository->markAllAsRead($userId);
    }
}