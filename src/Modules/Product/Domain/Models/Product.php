<?php

namespace Modules\Product\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Category\Domain\Models\Category;
use Modules\Product\Database\Factories\ProductFactory;
use Modules\Warehouse\Domain\Models\Warehouse;
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'uuid', 'name', 'description', 'price', 'category_id',
        'sku', 'weight', 'dimensions', 'material', 'color', 'status'
    ];

    protected $casts = [
        'status' => 'boolean',
        'price' => 'decimal:2',
    ];

    protected static function newFactory(): \Illuminate\Database\Eloquent\Factories\Factory
    {
        return ProductFactory::new();
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class, 'product_id')->where('is_primary', true);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'warehouse_product')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }
    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = $model->uuid ?? (string)\Illuminate\Support\Str::uuid());
    }
}
