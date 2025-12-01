<?php

namespace Modules\Role\Listeners;

use Modules\Role\Events\RoleAssigned;
use Modules\Shared\Services\CacheService; 
use Illuminate\Support\Facades\Log;

class InvalidatePermissionCache
{
    public function __construct(
        protected CacheService $cacheService
    ) {}

    public function handle(RoleAssigned $event): void
    {
        try {
            $cacheKey = "user_permissions_{$event->user->id}";
            $this->cacheService->forget($cacheKey);

            Log::info("Permission cache invalidated for User ID: {$event->user->id}");

        } catch (\Exception $e) {
            Log::error("Failed to invalidate permission cache: " . $e->getMessage());
        }
    }
}