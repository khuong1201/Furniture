<?php

namespace Modules\Promotion\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class PromotionProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['uuid', 'promotion_id', 'product_id'];

    protected static function booted()
    {
        static::creating(fn($model) => $model->uuid = $model->uuid ?? Str::uuid());
    }
}
