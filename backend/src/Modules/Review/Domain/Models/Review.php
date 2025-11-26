<?php

namespace Modules\Review\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\User\Domain\Models\User;
use Modules\Product\Domain\Models\Product;

class Review extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid', 'user_id', 'product_id', 'rating', 'comment', 'is_approved'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = $model->uuid ?? (string) Str::uuid());
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
