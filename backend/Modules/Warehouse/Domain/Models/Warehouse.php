<?php

namespace Modules\Warehouse\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Shared\Traits\Loggable;
use Modules\Inventory\Domain\Models\InventoryStock; 

class Warehouse extends Model
{
    use HasFactory, SoftDeletes, Loggable;

    protected $fillable = ['uuid', 'name', 'location', 'manager_id'];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = (string) Str::uuid());
    }

    public function stocks()
    {
        return $this->hasMany(InventoryStock::class, 'warehouse_id');
    }

    public function getTotalItemsAttribute()
    {
        return $this->stocks()->sum('quantity');
    }
}