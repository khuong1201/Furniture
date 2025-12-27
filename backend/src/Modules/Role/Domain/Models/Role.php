<?php

declare(strict_types=1);

namespace Modules\Role\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Modules\User\Domain\Models\User;
use Modules\Permission\Domain\Models\Permission;
use Modules\Shared\Traits\Loggable;

class Role extends Model
{
    use HasFactory, SoftDeletes, Loggable;

    protected $fillable = [
        'uuid', 'name', 'slug', 'description', 'guard_name', 'is_system', 'priority'
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'priority'  => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->uuid = $model->uuid ?: (string) Str::uuid();
        });
        
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user')
            ->withPivot('assigned_at', 'assigned_by')
            ->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id')
            ->withTimestamps();
    }
}