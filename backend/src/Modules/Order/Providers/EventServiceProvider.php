<?php

namespace Modules\Order\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Order\Events\OrderStatusUpdated;
use Modules\Order\Listeners\SyncShippingStatus;
use Modules\Order\Listeners\UpdateOrderStatusOnPayment; // Import listener này
use Modules\Payment\Events\PaymentCompleted; // Import event này
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        OrderStatusUpdated::class => [
            SyncShippingStatus::class,
        ],
        
        // Khi có Webhook thanh toán thành công -> Cập nhật trạng thái Order sang PAID/PROCESSING
        PaymentCompleted::class => [
            UpdateOrderStatusOnPayment::class,
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
