<?php

namespace Modules\Notification\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Order\Events\OrderCreated;
use Modules\Order\Events\OrderStatusUpdated;
use Modules\Inventory\Events\LowStockDetected;
use Modules\Notification\Listeners\SendOrderCreatedNotification;
use Modules\Notification\Listeners\SendOrderStatusNotification;
use Modules\Notification\Listeners\SendLowStockNotification;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        OrderCreated::class => [
            SendOrderCreatedNotification::class,
        ],

        OrderStatusUpdated::class => [
            SendOrderStatusNotification::class,
        ],

        LowStockDetected::class => [
            SendLowStockNotification::class,
        ],
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
