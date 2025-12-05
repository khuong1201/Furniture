<?php

declare(strict_types=1);

namespace Modules\Inventory\Events;

use Modules\Product\Domain\Models\ProductVariant;
use Modules\Warehouse\Domain\Models\Warehouse;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockDetected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ProductVariant $variant,
        public Warehouse $warehouse,
        public int $currentQuantity
    ) {}
}