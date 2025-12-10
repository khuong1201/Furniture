<?php

namespace Modules\Product\Listeners;

use Modules\Order\Events\OrderCreated;
use Modules\Product\Domain\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class IncrementProductSoldCount implements ShouldQueue
{
    public $queue = 'default';

    public function handle(OrderCreated $event): void
    {
        try {
            $order = $event->order;

            $order->load('items.variant');

            foreach ($order->items as $item) {
                $variant = $item->variant;
                if (!$variant) continue;

                $qty = $item->quantity;

                $variant->increment('sold_count', $qty);

                Product::where('id', $variant->product_id)->increment('sold_count', $qty);
            }
        } catch (\Exception $e) {
            Log::error("Error incrementing sold count for Order {$event->order->uuid}: " . $e->getMessage());
        }
    }
}