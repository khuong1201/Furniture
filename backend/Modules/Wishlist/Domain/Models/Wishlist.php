<?php

declare(strict_types=1);

namespace Modules\Wishlist\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\User\Domain\Models\User;
use Modules\Product\Domain\Models\Product;

class Wishlist extends Model
{
    use HasFactory;

    protected $fillable = ['uuid', 'user_id', 'product_id'];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn(Wishlist $model) => $model->uuid = (string) Str::uuid());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    protected static function newFactory()
    {
        return \Modules\Wishlist\Database\factories\WishlistFactory::new();
    }
}