<?php

namespace Modules\Address\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Modules\User\Domain\Models\User;
use Modules\Shared\Traits\Loggable;

class Address extends Model
{
    use HasFactory, SoftDeletes, Loggable;

    protected $fillable = [
        'uuid', 'user_id', 'full_name', 'phone',
        'province', 'district', 'ward', 'street', 'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function getFullAddressAttribute(): string
    {
        return "{$this->street}, {$this->ward}, {$this->district}, {$this->province}";
    }

    protected static function newFactory()
    {
        return \Modules\Address\Database\factories\AddressFactory::new();
    }
}