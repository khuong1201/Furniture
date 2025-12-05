<?php

declare(strict_types=1);

namespace Modules\Role\Listeners;

use Modules\Role\Events\RoleAssigned;
use Modules\User\Domain\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;

class InvalidatePermissionCache implements ShouldQueue
{
    public $queue = 'default';

    public function handle(RoleAssigned $event): void
    {
        try {
            if ($event->user instanceof User) {
                $event->user->clearPermissionCache();
                Log::info("Permission cache invalidated for User ID: {$event->user->id}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to invalidate permission cache: " . $e->getMessage());
        }
    }
}