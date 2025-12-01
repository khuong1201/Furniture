<?php

namespace Modules\Notification\Services;

use Modules\Shared\Services\BaseService;
use Modules\Notification\Domain\Repositories\NotificationRepositoryInterface;
use Illuminate\Support\Str;

class NotificationService extends BaseService
{
    public function __construct(NotificationRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getMyNotifications(int $userId, int $perPage = 15)
    {
        $notifications = $this->repository->getUserNotifications($userId, $perPage);
        $unreadCount = $this->repository->getUnreadCount($userId);
        return [
            'items' => $notifications,
            'unread_count' => $unreadCount
        ];
    }

    public function send(int $userId, string $title, string $content, string $type = 'info', array $data = [])
    {
        return $this->repository->create([
            'uuid' => Str::uuid(),
            'user_id' => $userId,
            'title' => $title,
            'content' => $content,
            'type' => $type,
            'data' => $data,
            'read_at' => null
        ]);
    }

    public function markAsRead(string $uuid, int $userId)
    {
        $notification = $this->repository->findByUuidAndUser($uuid, $userId);
        
        if ($notification) {
            $notification->markAsRead();
        }
        
        return $notification;
    }

    public function markAllAsRead(int $userId)
    {
        $this->repository->markAllAsRead($userId);
    }
}