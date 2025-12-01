<?php

namespace Modules\Payment\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Order\Domain\Models\Order;
use Modules\Shared\Traits\Loggable;

class Payment extends Model
{
    use HasFactory, SoftDeletes, Loggable;

    protected $fillable = [
        'uuid', 'order_id', 'method', 'amount', 'currency',
        'status', 'paid_at', 'transaction_id', 'payment_data'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'payment_data' => 'array',
        'amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = (string) Str::uuid());
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    protected static function newFactory()
    {
        return \Modules\Payment\Database\factories\PaymentFactory::new();
    }
}