<?php

namespace Modules\Notification\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\User\Domain\Models\User;
use Modules\Shared\Traits\Loggable;

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

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid = (string) Str::uuid());
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    public function markAsUnread(): void
    {
        $this->update(['read_at' => null]);
    }

    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }
}