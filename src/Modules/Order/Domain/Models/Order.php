<?php

namespace Modules\Order\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Order\Database\Factories\OrderFactory;
use Modules\Order\Domain\Models\OrderItem;
use Modules\User\Domain\Models\User;
use Modules\Address\Domain\Models\Address;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'address_id',
        'status',
        'payment_status',
        'shipping_status',
        'total_amount',
        'ordered_at',
    ];

    protected static function newFactory()
    {
        return OrderFactory::new();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
