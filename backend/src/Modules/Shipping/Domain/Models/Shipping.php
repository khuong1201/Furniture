<?php

declare(strict_types=1);

namespace Modules\Shipping\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Order\Domain\Models\Order;
use Modules\Shared\Traits\Loggable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipping extends Model
{
    use HasFactory, SoftDeletes, Loggable;

    protected $fillable = [
        'uuid', 
        'order_id', 
        'provider', 
        'tracking_number', 
        'status', 
        'fee',      
        'shipped_at', 
        'delivered_at',
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'fee' => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn(Shipping $model) => $model->uuid = (string) Str::uuid());
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    protected static function newFactory()
    {
        return \Modules\Shipping\Database\factories\ShippingFactory::new();
    }
}