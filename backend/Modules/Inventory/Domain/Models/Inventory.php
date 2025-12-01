<?php

namespace Modules\Inventory\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Product\Models\Product;
use Modules\Warehouse\Models\Warehouse;
use Modules\Shared\Traits\Loggable;

class Inventory extends Model
{
    use HasFactory, SoftDeletes, Loggable;

    protected $table = 'inventories';

    protected $fillable = [
        'uuid', 'product_id', 'warehouse_id', 
        'stock_quantity', 'min_threshold', 'max_threshold', 'status'
    ];

    protected $casts = [
        'stock_quantity' => 'integer',
        'min_threshold' => 'integer',
        'max_threshold' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = (string) Str::uuid());
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}