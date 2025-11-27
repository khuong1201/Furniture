<?php

namespace Modules\Notification\Http\Controllers;
use Illuminate\Http\Request;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Notification\Services\NotificationService;
use Modules\Notification\Http\Requests\NotificationRequest;

class NotificationController extends BaseController
{
    public function __construct(NotificationService $service)
    {
        parent::__construct($service);
    }

    protected function validateData(Request $request): array
    {
        return $request->validated();
    }
}