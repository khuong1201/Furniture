<?php

namespace Modules\Shipping\Events;

use Modules\Shipping\Domain\Models\Shipping;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShippingStatusUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Shipping $shipping) {}
}