<?php

namespace Modules\Cart\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\Product\Domain\Models\Product;

class CartItem extends Model
{
    protected $fillable = ['uuid', 'cart_id', 'product_id', 'quantity'];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = (string) Str::uuid());
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function newFactory()
    {
        return \Modules\CartItem\Database\factories\CartItemFactory::new();
    }
}