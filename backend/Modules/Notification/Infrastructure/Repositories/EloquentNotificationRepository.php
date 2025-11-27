<?php

namespace Modules\Notification\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Notification\Domain\Models\Notification;
use Modules\Notification\Domain\Repositories\NotificationRepository;
use Illuminate\Database\Eloquent\Builder;

class EloquentNotificationRepository extends EloquentBaseRepository implements NotificationRepository
{
    public function __construct(Notification $model)
    {
        parent::__construct($model);
    }
    public function query(): Builder
    {
        return parent::query()->where('user_id', auth()->id());
    }
}