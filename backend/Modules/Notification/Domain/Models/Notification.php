<?php

declare(strict_types=1);

namespace Modules\Notification\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\User\Domain\Models\User;
use Modules\Shared\Traits\Loggable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use SoftDeletes, Loggable;

    protected $fillable = [
        'uuid', 'user_id', 'title', 'content', 'type', 'data', 'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn(Notification $model) => $model->uuid = (string) Str::uuid());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }
}