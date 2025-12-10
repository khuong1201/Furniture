<?php

declare(strict_types=1);

namespace Modules\Review\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Modules\User\Domain\Models\User;
use Modules\Product\Domain\Models\Product;
use Modules\Shared\Traits\Loggable;

class Review extends Model
{
    use HasFactory, SoftDeletes, Loggable;

    protected $fillable = [
        'uuid', 'user_id', 'product_id', 'order_id', 'rating', 'comment', 'images', 'is_approved'
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
        'images' => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn(Review $model) => $model->uuid = (string) Str::uuid());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function newFactory()
    {
        return \Modules\Review\Database\factories\ReviewFactory::new();
    }
}