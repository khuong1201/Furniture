<?php

declare(strict_types=1);

namespace Modules\Notification\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Events\LowStockDetected;
use Modules\Notification\Services\NotificationService;
use Modules\User\Domain\Models\User;

class SendLowStockNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'notifications';

    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function handle(LowStockDetected $event): void
    {
        try {
            $variant = $event->variant;
            $warehouse = $event->warehouse;

            if (!$variant->relationLoaded('product')) {
                $variant->load('product');
            }

            $productName   = $variant->product->name ?? 'Unknown Product';
            $sku           = $variant->sku;
            $warehouseName = $warehouse->name;
            $currentQty    = $event->currentQuantity;

            $recipients = User::role(['admin', 'inventory_manager'])->get();

            foreach ($recipients as $user) {
                $this->notificationService->send(
                    userId: $user->id,
                    title: 'Low Stock Alert',
                    content: "Product: {$productName} ({$sku}) at warehouse {$warehouseName} has only {$currentQty} items left.",
                    type: 'warning',
                    data: [
                        'variant_uuid'   => $variant->uuid,
                        'warehouse_uuid' => $warehouse->uuid,
                        'action'         => 'restock',
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::error('LowStockNotification Error: ' . $e->getMessage());
        }
    }
}