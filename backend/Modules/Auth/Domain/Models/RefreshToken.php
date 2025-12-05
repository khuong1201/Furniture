<?php

declare(strict_types=1);

namespace Modules\Auth\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\User\Domain\Models\User;

class RefreshToken extends Model
{
    protected $fillable = [
        'user_id', 'token', 'device_name', 'ip', 'user_agent', 'expires_at', 'is_revoked'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_revoked' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function isValid(): bool
    {
        return !$this->is_revoked && $this->expires_at->isFuture();
    }
}