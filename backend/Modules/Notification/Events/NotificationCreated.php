<?php

declare(strict_types=1);

namespace Modules\Notification\Events;

use Modules\Notification\Domain\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Notification $notification) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $this->notification->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    public function broadcastWith(): array
    {
        return [
            'uuid'       => $this->notification->uuid,
            'title'      => $this->notification->title,
            'content'    => $this->notification->content,
            'type'       => $this->notification->type,
            'data'       => $this->notification->data,
            'read_at'    => null,
            'created_at' => $this->notification->created_at->toIso8601String(),
        ];
    }
}