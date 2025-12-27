<?php

declare(strict_types=1);

namespace Modules\Order\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Order\database\factories\OrderFactory;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Enums\PaymentStatus;
use Modules\Shared\Traits\Loggable;
use Modules\User\Domain\Models\User;
use Modules\Payment\Domain\Models\Payment;
use Modules\Shipping\Domain\Models\Shipping;
use Modules\Order\Domain\Models\OrderItem;

class Order extends Model
{
    use HasFactory, SoftDeletes, Loggable;

    protected $fillable = [
        'uuid', 'user_id', 'code',
        'shipping_name', 'shipping_phone', 'shipping_address_snapshot', // <--- THÊM MỚI
        'status', 'payment_status', 'shipping_status',
        'subtotal', 'shipping_fee', 'grand_total',
        'notes', 'voucher_code', 'voucher_discount',
        'ordered_at'
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'payment_status' => PaymentStatus::class,
        'shipping_address_snapshot' => 'array',
        'ordered_at' => 'datetime',
        'subtotal' => 'integer',
        'shipping_fee' => 'integer',
        'grand_total' => 'integer',
        'voucher_discount' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn(Order $model) => $model->uuid = (string) Str::uuid());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipping(): HasOne
    {
        return $this->hasOne(Shipping::class); 
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }
}