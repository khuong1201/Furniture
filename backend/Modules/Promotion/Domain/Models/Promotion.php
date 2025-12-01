<?php

namespace Modules\Promotion\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Modules\Product\Domain\Models\Product;
use Modules\Shared\Traits\Loggable;

class Promotion extends Model
{
    use HasFactory, SoftDeletes, Loggable;

    protected $fillable = [
        'uuid', 'name', 'description', 'type', 'value', 
        'start_date', 'end_date', 'status'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'status' => 'boolean',
        'value' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = (string) Str::uuid());
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'promotion_product');
    }
    
    public function scopeActive($query)
    {
        return $query->where('status', true)
                     ->where('start_date', '<=', now())
                     ->where('end_date', '>=', now());
    }
}