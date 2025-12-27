<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Product\Domain\Models\ProductVariant;
use Modules\User\Domain\Models\User;
use Modules\Warehouse\Domain\Models\Warehouse;

class InventoryLog extends Model
{
    protected $table = 'inventory_logs';

    protected $fillable = [
        'warehouse_id', 
        'product_variant_id', 
        'user_id',
        'previous_quantity', 
        'new_quantity', 
        'quantity_change',
        'type', 
        'reason'
    ];

    protected $casts = [
        'previous_quantity' => 'integer',
        'new_quantity'      => 'integer',
        'quantity_change'   => 'integer',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}