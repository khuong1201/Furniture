<?php

namespace Modules\Cart\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\Product\Domain\Models\ProductVariant; 
use Modules\Shared\Traits\Loggable;

class CartItem extends Model
{
    use Loggable;
    
    protected $fillable = ['uuid', 'cart_id', 'product_variant_id', 'quantity', 'price'];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = (string) Str::uuid());
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}