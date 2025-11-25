<?php

namespace Modules\Role\Listeners;

use Modules\Role\Events\RoleAssigned;
use Modules\User\Application\IUserService;

class InvalidatePermissionCache
{
    public function __construct(private IUserService $userService) {}

    public function handle(RoleAssigned $event)
    {
        $this->userService->invalidatePermissionCache($event->user);
    }
}