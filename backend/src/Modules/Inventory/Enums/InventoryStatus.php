<?php

declare(strict_types=1);

namespace Modules\Inventory\Enums;

enum InventoryStatus: string
{
    case OUT_OF_STOCK = 'out_of_stock';
    case LOW_STOCK    = 'low_stock';
    case IN_STOCK     = 'in_stock';
    case OLD_STOCK    = 'old_stock';

    public function color(): string
    {
        return match($this) {
            self::OUT_OF_STOCK => 'danger',
            self::LOW_STOCK    => 'warning',
            self::OLD_STOCK    => 'info',     // xanh nhạt / xám
            self::IN_STOCK     => 'success',
        };
    }

    public function label(): string
    {
        return match($this) {
            self::OUT_OF_STOCK => 'Out of Stock',
            self::LOW_STOCK    => 'Low Stock',
            self::OLD_STOCK    => 'Old Stock',
            self::IN_STOCK     => 'In Stock',
        };
    }
}