<?php

namespace Modules\Log\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Log\Events\ModelActionLogged;
use Modules\Log\Events\SystemErrorLogged;
use Modules\Log\Listeners\WriteModelActionLog;
use Modules\Log\Listeners\WriteSystemErrorLog;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        ModelActionLogged::class => [WriteModelActionLog::class],
        SystemErrorLogged::class => [WriteSystemErrorLog::class],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
