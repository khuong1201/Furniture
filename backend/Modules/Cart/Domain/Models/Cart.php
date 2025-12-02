<?php

namespace Modules\Cart\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Shared\Traits\Loggable;
use Illuminate\Support\Str;
use Modules\User\Domain\Models\User;

class Cart extends Model
{
    use HasFactory, Loggable; 

    protected $fillable = ['uuid', 'user_id'];

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
        return $this->hasMany(CartItem::class);
    }

    protected static function newFactory()
    {
        return \Modules\Cart\Database\factories\CartFactory::new();
    }
}