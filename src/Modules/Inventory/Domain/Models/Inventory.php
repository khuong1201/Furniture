<?php

namespace Modules\Inventory\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventories';

    protected $fillable = [
        'uuid','product_id','warehouse_id','stock_quantity','min_threshold','max_threshold','status'
    ];

    protected $casts = [
        'stock_quantity' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(\Modules\Product\Domain\Entities\Product::class, 'product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(\Modules\Warehouse\Domain\Entities\Warehouse::class, 'warehouse_id');
    }
}
