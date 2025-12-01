<?php

namespace Modules\Order\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Product\Domain\Models\Product;
use Modules\Warehouse\Domain\Models\Warehouse;

class OrderItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'order_id', 'product_id', 'warehouse_id', 
        'quantity', 'unit_price', 'subtotal', 'original_price', 'discount_amount'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = (string) Str::uuid());
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}