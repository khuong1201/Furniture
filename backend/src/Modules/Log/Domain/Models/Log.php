<?php

declare(strict_types=1);

namespace Modules\Log\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Modules\User\Domain\Models\User;

class Log extends Model
{
    protected $fillable = [
        'uuid', 'user_id', 'type', 'action', 'model', 'model_uuid',
        'ip_address', 'message', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'user_id'  => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn (Log $log) => $log->uuid = (string) Str::uuid());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}