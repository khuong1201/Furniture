<?php

declare(strict_types=1);

namespace Modules\Inventory\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Product\Domain\Models\ProductVariant;
use Modules\Inventory\Domain\Models\Warehouse;  

class LowStockDetected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ProductVariant $variant,
        public Warehouse $warehouse,
        public int $currentQuantity
    ) {}
}