<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Modules\Product\Domain\Models\ProductVariant;
use Modules\Warehouse\Domain\Models\Warehouse;
use Modules\Shared\Traits\Loggable;

class InventoryStock extends Model
{
    use HasFactory, Loggable;

    protected $table = 'inventory_stocks';

    protected $fillable = [
        'uuid', 'warehouse_id', 'product_variant_id', 
        'quantity', 'min_threshold'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'min_threshold' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn(InventoryStock $model) => $model->uuid = (string) Str::uuid());
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
    
    public function getStatusAttribute(): string
    {
        if ($this->quantity <= 0) return 'out_of_stock';
        if ($this->quantity <= $this->min_threshold) return 'low_stock';
        return 'in_stock';
    }
}