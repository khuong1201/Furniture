<?php
namespace Modules\Product\Domain\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Shared\Traits\Loggable;
use Modules\Category\Domain\Models\Category;
use Modules\Promotion\Domain\Models\Promotion; 
use Modules\Review\Domain\Models\Review;

class Product extends Model {
    use SoftDeletes, Loggable;
    protected $fillable = ['uuid', 'name', 'description', 'category_id', 'has_variants', 'is_active', 'price', 'sku'];
    protected $casts = ['is_active' => 'boolean', 'has_variants' => 'boolean', 'price' => 'decimal:2'];
    protected static function boot() {
        parent::boot();
        static::creating(fn($m) => $m->uuid = (string) Str::uuid());
    }
    public function category() { return $this->belongsTo(Category::class); }
    public function variants() { return $this->hasMany(ProductVariant::class); }
    public function images() { return $this->hasMany(ProductImage::class)->orderByDesc('is_primary'); }

    public function promotions() { return $this->belongsToMany(Promotion::class, 'product_promotion');}
    public function reviews() { return $this->hasMany(Review::class);}
}