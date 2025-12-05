<?php

declare(strict_types=1);

namespace Modules\Notification\Listeners;

use Modules\Inventory\Events\LowStockDetected;
use Modules\Notification\Services\NotificationService;
use Modules\User\Domain\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendLowStockNotification implements ShouldQueue
{
    public $queue = 'default';

    public function __construct(protected NotificationService $notificationService) {}

    public function handle(LowStockDetected $event): void
    {
        $variant = $event->variant;
        $variant->load('product'); 
        
        $productName = $variant->product->name;
        $sku = $variant->sku;
        $warehouseName = $event->warehouse->name;

        // Logic: Gửi cho Admin và Staff
        // Cẩn thận N+1 nếu nhiều user, nhưng thông báo admin thường ít user
        $recipients = User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'manager']))->get();

        foreach ($recipients as $user) {
            $this->notificationService->send(
                $user->id,
                'Cảnh báo tồn kho thấp',
                "Sản phẩm {$productName} (SKU: {$sku}) tại {$warehouseName} chỉ còn {$event->currentQuantity} sản phẩm.",
                'warning',
                ['variant_uuid' => $variant->uuid, 'type' => 'inventory_alert']
            );
        }
    }
}