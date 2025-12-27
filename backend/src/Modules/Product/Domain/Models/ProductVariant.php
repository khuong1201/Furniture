<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\Inventory\Domain\Models\InventoryStock;

class ProductVariant extends Model 
{
    // FIX: Thêm 'name' vào đây
    protected $fillable = [
        'uuid', 
        'product_id', 
        'sku', 
        'name', 
        'price', 
        'weight', 
        'image_url', 
        'sold_count'
    ];

    protected $casts = [
        'price' => 'integer',
        'weight' => 'decimal:2',
    ];

    protected static function boot(): void 
    { 
        parent::boot(); 
        static::creating(fn($m) => $m->uuid = (string) Str::uuid()); 
    }

    public function product(): BelongsTo 
    { 
        return $this->belongsTo(Product::class); 
    }

    public function attributeValues(): BelongsToMany 
    { 
        return $this->belongsToMany(AttributeValue::class, 'variant_attribute_values'); 
    }

    public function stock(): HasMany
    {
        return $this->hasMany(InventoryStock::class, 'product_variant_id');
    }
}