<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Modules\Shared\Traits\Loggable;

class ProductImage extends Model 
{
    use Loggable;
    
    protected $fillable = ['uuid', 'product_id', 'image_url', 'public_id', 'is_primary', 'sort_order'];

    protected static function boot(): void 
    {
        parent::boot();
        static::creating(fn(ProductImage $m) => $m->uuid = (string) Str::uuid());
    }
    
    public function product(): BelongsTo 
    { 
        return $this->belongsTo(Product::class); 
    }
}