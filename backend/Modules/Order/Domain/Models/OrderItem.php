<?php

declare(strict_types=1);

namespace Modules\Order\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Product\Domain\Models\ProductVariant;
use Modules\Warehouse\Domain\Models\Warehouse;

class OrderItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'order_id', 'product_variant_id', 'warehouse_id', 
        'quantity', 'unit_price', 'subtotal', 'original_price', 
        'discount_amount', 'product_snapshot'
    ];

    protected $casts = [
        'product_snapshot' => 'array',
        'unit_price' => 'integer',
        'subtotal' => 'integer',
        'original_price' => 'integer',
        'discount_amount' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = (string) Str::uuid());
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}