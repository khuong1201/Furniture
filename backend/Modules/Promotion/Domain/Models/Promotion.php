<?php

declare(strict_types=1);

namespace Modules\Promotion\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Modules\Product\Domain\Models\Product;
use Modules\Shared\Traits\Loggable;
use Illuminate\Database\Eloquent\Builder;

class Promotion extends Model
{
    use HasFactory, SoftDeletes, Loggable;

    protected $fillable = [
        'uuid', 'name', 'description', 'type', 'value', 
        'min_order_value', 'max_discount_amount',
        'quantity', 'used_count', 'limit_per_user',
        'start_date', 'end_date', 'is_active'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'value' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn(Promotion $model) => $model->uuid = (string) Str::uuid());
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_promotion');
    }
    
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
                     ->where('start_date', '<=', now())
                     ->where('end_date', '>=', now());
    }

    /**
     * Check if promotion is valid for application
     */
    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        
        $now = now();
        if ($this->start_date > $now || $this->end_date < $now) return false;

        if ($this->quantity > 0 && $this->used_count >= $this->quantity) return false;

        return true;
    }

    protected static function newFactory()
    {
        return \Modules\Promotion\Database\factories\PromotionFactory::new();
    }
}