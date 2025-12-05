<?php

declare(strict_types=1);

namespace Modules\Currency\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Shared\Traits\Loggable;

class Currency extends Model
{
    use HasFactory, SoftDeletes, Loggable;

    protected $fillable = [
        'code', 'symbol', 'name', 'exchange_rate', 'is_default', 'is_active'
    ];

    protected $casts = [
        'exchange_rate' => 'float',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    protected static function newFactory()
    {
        return \Modules\Currency\Database\factories\CurrencyFactory::new();
    }
}