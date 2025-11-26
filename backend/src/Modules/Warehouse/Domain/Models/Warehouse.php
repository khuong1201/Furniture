<?php

namespace Modules\Warehouse\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Warehouse\Database\Factories\WarehouseFactory;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'warehouses';

    protected $fillable = [
        'uuid', 'name', 'location', 'manager_id'
    ];

    protected $casts = [
    ];

    public function products()
    {
        return $this->belongsToMany(\Modules\Product\Domain\Models\Product::class, 'warehouse_product')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function inventories()
    {
        return $this->hasMany(\Modules\Inventory\Domain\Models\Inventory::class, 'warehouse_id');
    }

    public function manager()
    {
        return $this->belongsTo(\Modules\User\Domain\Models\User::class, 'manager_id');
    }

    protected static function newFactory(): Factory
    {
        return WarehouseFactory::new();
    }
}
