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
        \Modules\Auth\Events\UserRegistered::class => [
            \Modules\Notification\Listeners\SendOtpOnRegister::class,
        ],
        \Modules\Order\Events\OrderCreated::class => [
            \Modules\Notification\Listeners\SendOrderSuccessNotification::class,
        ],
        \Modules\Order\Events\OrderCancelled::class => [
            \Modules\Notification\Listeners\SendOrderCancelledNotification::class,
        ],

        \Modules\Shipping\Events\ShippingStatusUpdated::class => [
            \Modules\Notification\Listeners\SendShippingUpdateNotification::class,
        ],
        \Modules\Payment\Events\PaymentCompleted::class => [
            \Modules\Order\Listeners\UpdateOrderStatusOnPayment::class,
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
