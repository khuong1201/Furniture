<?php

namespace Modules\Order\Events;

use Modules\Order\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCancelled
{
    use Dispatchable, SerializesModels;

    public function __construct(public Order $order) {}
}