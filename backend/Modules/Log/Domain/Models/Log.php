<?php

namespace Modules\Log\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Modules\User\Domain\Models\User;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid', 'user_id', 'type', 'action', 'model', 'model_uuid',
        'ip_address', 'message', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($log) => $log->uuid = (string) Str::uuid());
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}