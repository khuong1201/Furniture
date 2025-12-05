<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Đã chuyển vào Trait
use Illuminate\Support\Str;
use Modules\Shared\Traits\Loggable; // Giả sử có trait này
use Modules\Category\Domain\Models\Category;
use Modules\Review\Domain\Models\Review;
use Modules\Promotion\Domain\Traits\HasPromotions; // IMPORT TRAIT

class Product extends Model 
{
    use SoftDeletes, Loggable;
    use HasPromotions; // SỬ DỤNG TRAIT

    protected $fillable = [
        'uuid', 'name', 'description', 'category_id', 'has_variants', 
        'is_active', 'price', 'sku', 'sold_count', 'rating_avg', 'rating_count'
    ];
    
    protected $casts = [
        'is_active'    => 'boolean', 
        'has_variants' => 'boolean', 
        'price'        => 'integer',
        'rating_avg'   => 'decimal:2'
    ];

    protected $appends = ['flash_sale_info']; 

    protected static function boot(): void 
    {
        parent::boot();
        static::creating(fn(Product $m) => $m->uuid = (string) Str::uuid());
    }

    public function category(): BelongsTo 
    { 
        return $this->belongsTo(Category::class); 
    }
    
    public function variants(): HasMany 
    { 
        return $this->hasMany(ProductVariant::class); 
    }
    
    public function images(): HasMany 
    { 
        return $this->hasMany(ProductImage::class)->orderByDesc('is_primary')->orderBy('sort_order'); 
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}