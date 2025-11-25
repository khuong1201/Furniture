<?php

namespace Modules\Notification\Services;

use Modules\Shared\Services\BaseService;
use Modules\Notification\Domain\Repositories\INotificationRepository;

class NotificationService extends BaseService
{
    public function __construct(INotificationRepository $repository)
    {
        parent::__construct($repository);
    }
}
