<?php

namespace Modules\Order\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Order\Infrastructure\Database\Factories\OrderItemFactory;
use Modules\Product\Domain\Models\Product;
use Modules\Warehouse\Domain\Models\Warehouse;
class OrderItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'order_id',
        'product_id',
        'warehouse_id',
        'quantity',
        'unit_price',
    ];

    protected static function newFactory()
    {
        return OrderItemFactory::new();
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
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
