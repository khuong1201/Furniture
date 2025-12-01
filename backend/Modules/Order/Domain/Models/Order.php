<?php

namespace Modules\Order\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\User\Domain\Models\User;
use Modules\Shared\Traits\Loggable;

class Order extends Model
{
    use HasFactory, SoftDeletes, Loggable;

    protected $fillable = [
        'uuid', 'user_id', 'shipping_address_snapshot', 
        'status', 'payment_status', 'shipping_status', 
        'total_amount', 'ordered_at', 'notes'
    ];

    protected $casts = [
        'shipping_address_snapshot' => 'array',
        'ordered_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = (string) Str::uuid());
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}