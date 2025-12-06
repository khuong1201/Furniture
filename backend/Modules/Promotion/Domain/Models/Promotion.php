<?php

declare(strict_types=1);

namespace Modules\Promotion\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Modules\Product\Domain\Models\Product;
use Modules\Promotion\Database\Factories\PromotionFactory; // Cần đảm bảo namespace factory đúng
use Illuminate\Database\Eloquent\Builder;

class Promotion extends Model
{
    use HasFactory, SoftDeletes; // Giả sử Loggable nằm ở Shared, nếu không có file Shared thì bỏ qua

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'type',
        'value',
        'min_order_value',
        'max_discount_amount',
        'quantity',
        'used_count',
        'limit_per_user',
        'start_date',
        'end_date',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        // CHANGE: Cast về integer (BigInt)
        'value' => 'integer',
        'min_order_value' => 'integer',
        'max_discount_amount' => 'integer',
        'quantity' => 'integer',
        'used_count' => 'integer',
        'limit_per_user' => 'integer',
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

    /**
     * Scope lấy các promotion đang active và trong thời gian hiệu lực
     */
    public function scopeActive(Builder $query): Builder
    {
        $now = now();
        return $query->where('promotions.is_active', true)
            ->where('promotions.start_date', '<=', $now)
            ->where('promotions.end_date', '>=', $now);
    }

    /**
     * Kiểm tra logic hợp lệ (số lượng, thời gian)
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        if ($this->start_date > $now || $this->end_date < $now) {
            return false;
        }

        if ($this->quantity > 0 && $this->used_count >= $this->quantity) {
            return false;
        }

        return true;
    }

    protected static function newFactory()
    {
        return PromotionFactory::new();
    }
}