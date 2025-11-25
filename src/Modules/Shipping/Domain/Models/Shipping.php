<?php

namespace Modules\Shipping\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Shipping extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'order_id',
        'provider',
        'tracking_number',
        'status',
        'shipped_at',
        'delivered_at',
    ];

    protected static function booted()
    {
        static::creating(fn($shipping) => $shipping->uuid = (string) Str::uuid());
    }

    public function order()
    {
        return $this->belongsTo(\Modules\Order\Domain\Models\Order::class);
    }

    protected static function newFactory()
    {
        return \Modules\Shipping\database\factories\ShippingFactory::new();
    }
}
