<?php

declare(strict_types=1);

namespace Modules\Voucher\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\Shared\Traits\Loggable;

class Voucher extends Model
{
    use SoftDeletes, Loggable;

    protected $fillable = [
        'uuid', 'code', 'name', 'description', 'type', 'value',
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
        static::creating(fn(Voucher $model) => $model->uuid = (string) Str::uuid());
    }

    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        
        // Check quantity limit (0 = unlimited if logic allows, but here quantity means total available)
        if ($this->quantity > 0 && $this->used_count >= $this->quantity) {
            return false;
        }
        
        $now = now();
        if ($this->start_date && $this->start_date > $now) return false;
        if ($this->end_date && $this->end_date < $now) return false;

        return true;
    }

    public function usages(): HasMany
    {
        return $this->hasMany(VoucherUsage::class);
    }
}