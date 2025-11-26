<?php

namespace Modules\Notification\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Notification\Domain\Models\Notification;
use Modules\Notification\Domain\Repositories\INotificationRepository;

class NotificationRepository extends EloquentBaseRepository implements INotificationRepository
{
    public function __construct(Notification $model)
    {
        parent::__construct($model);
    }
}
