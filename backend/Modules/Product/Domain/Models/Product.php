<?php

namespace Modules\Product\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Review\Domain\Models\Review;
use Modules\Category\Domain\Models\Category;
use Modules\Warehouse\Domain\Models\Warehouse; 
use Modules\Shared\Traits\Loggable;

class Product extends Model
{
    use HasFactory, SoftDeletes, Loggable;

    protected $fillable = [
        'uuid', 'name', 'description', 'price', 'category_id',
        'sku', 'weight', 'dimensions', 'material', 'color', 'status'
    ];

    protected $casts = [
        'status' => 'boolean',
        'price' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = (string) Str::uuid());
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderByDesc('is_primary');
    }
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'warehouse_product')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }
    
    public function getTotalStockAttribute(): int
    {
        return $this->warehouses->sum('pivot.quantity');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function promotions()
    {
        return $this->belongsToMany(\Modules\Promotion\Domain\Models\Promotion::class);
    }

    protected static function newFactory()
    {
        return \Modules\Product\Database\factories\ProductFactory::new();
    }
}