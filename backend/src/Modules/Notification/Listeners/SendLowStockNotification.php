<?php

declare(strict_types=1);

namespace Modules\Notification\Listeners;

use Modules\Inventory\Events\LowStockDetected;
use Modules\Notification\Services\NotificationService;
use Modules\User\Domain\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendLowStockNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'notifications';

    public function __construct(protected NotificationService $notificationService) {}

    public function handle(LowStockDetected $event): void
    {
        try {
            $variant = $event->variant;
            $warehouse = $event->warehouse;
            
            // Eager load product nếu chưa có
            if (!$variant->relationLoaded('product')) {
                $variant->load('product');
            }
            
            $productName = $variant->product->name ?? 'Unknown Product';
            $sku = $variant->sku;
            $warehouseName = $warehouse->name;
            $recipients = User::role(['admin', 'inventory_manager'])->get();

            foreach ($recipients as $user) {
                $this->notificationService->send(
                    userId: $user->id,
                    title: 'Cảnh báo tồn kho',
                    content: "SP: {$productName} ({$sku}) tại kho {$warehouseName} chỉ còn {$event->currentQuantity}.",
                    type: 'warning',
                    data: [
                        'variant_uuid' => $variant->uuid,
                        'warehouse_uuid' => $warehouse->uuid,
                        'action' => 'restock'
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::error("LowStockNotification Error: " . $e->getMessage());
        }
    }
}