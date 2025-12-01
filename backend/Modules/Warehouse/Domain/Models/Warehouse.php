<?php

namespace Modules\Warehouse\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Product\Models\Product;
use Modules\User\Domain\Models\User;
use Modules\Shared\Traits\Loggable;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes, Loggable;

    protected $fillable = [
        'uuid', 'name', 'location', 'manager_id'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = (string) Str::uuid());
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'warehouse_product')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function getTotalItemsAttribute()
    {
        return $this->products()->sum('quantity');
    }

    protected static function newFactory()
    {
        return \Modules\Warehouse\Database\factories\WarehouseFactory::new();
    }
}