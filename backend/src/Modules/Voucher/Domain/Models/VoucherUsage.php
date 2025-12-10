<?php

declare(strict_types=1);

namespace Modules\Voucher\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherUsage extends Model
{
    public $timestamps = false; 

    protected $fillable = [
        'voucher_id', 'user_id', 'order_id', 'discount_amount', 'used_at'
    ];
    
    protected $casts = [
        'used_at' => 'datetime',
        'discount_amount' => 'decimal:2'
    ];
}