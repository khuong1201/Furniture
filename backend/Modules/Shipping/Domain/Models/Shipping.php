<?php

namespace Modules\Shipping\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Order\Domain\Models\Order;
use Modules\Shared\Traits\Loggable;

class Shipping extends Model
{
    use HasFactory, SoftDeletes, Loggable;

    protected $fillable = [
        'uuid', 'order_id', 'provider', 'tracking_number', 
        'status', 'shipped_at', 'delivered_at',
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
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
}