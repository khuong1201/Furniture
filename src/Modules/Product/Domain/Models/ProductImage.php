<?php

namespace Modules\Product\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Product\Database\Factories\ProductImageFactory;

class ProductImage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_images';

    protected $fillable = [
        'uuid', 'product_id', 'image_url', 'public_id', 'is_primary'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    protected static function newFactory(): \Illuminate\Database\Eloquent\Factories\Factory
    {
        return ProductImageFactory::new();
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}