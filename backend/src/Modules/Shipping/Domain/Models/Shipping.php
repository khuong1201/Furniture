<?php

namespace Modules\Shipping\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Order\Domain\Models\Order;

class Shipping extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'order_id', 'tracking_number', 'provider', 
        'status', 'fee', 'consignee_name', 'consignee_phone', 
        'address', 'city', 'district', 'ward', 
        'shipped_at', 'delivered_at'
    ];

    protected $casts = [
        'fee'          => 'float',
        'shipped_at'   => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // Accessor: Gộp địa chỉ hiển thị (dùng trong Resource)
    public function getAddressFullAttribute()
    {
        return collect([$this->address, $this->ward, $this->district, $this->city])
            ->filter()
            ->implode(', ');
    }

    // Relation
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}