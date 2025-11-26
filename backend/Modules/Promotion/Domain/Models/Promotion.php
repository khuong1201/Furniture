<?php

namespace Modules\Promotion\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Product\Domain\Models\Product;
use Illuminate\Support\Str;

class Promotion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'type',
        'value',
        'start_date',
        'end_date',
        'status'
    ];

    protected static function booted()
    {
        static::creating(fn($model) => $model->uuid = $model->uuid ?? Str::uuid());
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'promotion_products')->withTimestamps();
    }
}
