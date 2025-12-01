<?php

namespace Modules\Product\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductImage extends Model
{
    protected $fillable = ['uuid', 'product_id', 'image_url', 'public_id', 'is_primary'];
    
    protected $casts = ['is_primary' => 'boolean'];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = (string) Str::uuid());
    }
}