<?php

namespace Modules\Notification\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        \Modules\Inventory\Events\LowStockDetected::class => [
            \Modules\Notification\Listeners\SendLowStockNotification::class,
        ],
        \Modules\Order\Events\OrderCreated::class => [
            \Modules\Notification\Listeners\SendOrderCreateNotificationListener::class,
        
            \Modules\Notification\Listeners\SendOrderEmailListener::class,
        ],

        \Modules\Order\Events\OrderCancelled::class => [
            \Modules\Notification\Listeners\SendOrderCancelNotificationListener::class,
        ],

        \Modules\Order\Events\OrderStatusUpdated::class => [
            \Modules\Notification\Listeners\SendOrderStatusNotification::class,
        ],
        \Modules\Review\Events\ReviewPosted::class => [
            \Modules\Notification\Listeners\SendReviewNotification::class,
        ],
        \Modules\Payment\Events\PaymentCompleted::class => [
            \Modules\Notification\Listeners\SendPaymentSuccessNotification::class,
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
