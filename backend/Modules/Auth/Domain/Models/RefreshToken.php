<?php

namespace Modules\Auth\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\User\Domain\Models\User;
use Modules\Shared\Traits\Loggable;

class RefreshToken extends Model
{
    use Loggable;

    protected $fillable = [
        'user_id', 'token', 'device_name', 'ip', 'user_agent', 'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}