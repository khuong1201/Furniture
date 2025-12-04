<?php
namespace Modules\Product\Domain\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\Shared\Traits\Loggable;
use Modules\Inventory\Domain\Models\InventoryStock;

class ProductVariant extends Model {
    use Loggable;
    protected $fillable = ['uuid', 'product_id', 'sku', 'price', 'weight', 'image_url', 'sold_count'];
    protected static function boot() {
        parent::boot();
        static::creating(fn($m) => $m->uuid = (string) Str::uuid());
    }
    public function product() { return $this->belongsTo(Product::class); }
    public function attributeValues() { return $this->belongsToMany(AttributeValue::class, 'variant_attribute_values'); }
    public function stock() { return $this->hasMany(InventoryStock::class, 'product_variant_id'); }
}