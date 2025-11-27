<?php

namespace Modules\Notification\Services;

use Modules\Shared\Services\BaseService;
use Modules\Notification\Domain\Repositories\NotificationRepository;

class NotificationService extends BaseService
{
    public function __construct(NotificationRepository $repository)
    {
        parent::__construct($repository);
    }

    public function create(array $data)
    {
        $data['user_id'] = auth()->id();
        return parent::create($data);
    }
}