<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\Shared\Traits\Loggable; 
use Modules\Category\Domain\Models\Category;
use Modules\Review\Domain\Models\Review;
use Modules\Promotion\Domain\Traits\HasPromotions; 
use Modules\Brand\Domain\Models\Brand;
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Modules\Product\database\factories\ProductFactory;

class Product extends Model 
{
    use SoftDeletes, Loggable, HasFactory;
    use HasPromotions; 

    protected $fillable = [
        'uuid', 
        'name', 
        'slug', 
        'description', 
        'category_id', 
        'brand_id',
        'has_variants', 
        'is_active', 
        'price', 
        'sku', 
        'sold_count', 
        'rating_avg', 
        'rating_count'
    ];
    
    protected $casts = [
        'is_active'    => 'boolean', 
        'has_variants' => 'boolean', 
        'price'        => 'integer',
        'rating_avg'   => 'decimal:2'
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }
    
    protected $appends = ['flash_sale_info']; 

    protected static function boot(): void 
    {
        parent::boot();
        static::creating(function(Product $m) {
            $m->uuid = (string) Str::uuid();
            
            if (empty($m->slug)) {
                $m->slug = Str::slug($m->name);
            }
        });
        
        static::updating(function(Product $m) {
            if ($m->isDirty('name') && !$m->isDirty('slug')) {
                $m->slug = Str::slug($m->name);
            }
        });
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

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    protected static function newFactory()
    {
        return ProductFactory::new();
    }
}