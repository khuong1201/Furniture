<?php

declare(strict_types=1);

namespace Modules\User\Domain\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;
use Modules\Role\Domain\Models\Role;
use Modules\Shared\Traits\Loggable;
use Modules\Address\Domain\Models\Address;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, Loggable;

    protected $fillable = [
        'uuid', 'name', 'email', 'phone', 'password', 'avatar_url', 'is_active',
    ];

    protected $hidden = [
        'password', 'remember_token', 'verification_code', 'verification_expires_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
        'verification_expires_at' => 'datetime',
        'password' => 'hashed', 
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($user) {
            $user->uuid = $user->uuid ?: (string) Str::uuid();
        });
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id')
            ->withTimestamps();
    }

    public function permissions()
    {
        return $this->roles()->with('permissions');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'user_id');
    }

    public function allPermissions(): Collection
    {
        return Cache::remember(
            $this->getPermissionCacheKey(),
            3600, 
            function () {
                return $this->roles()->with('permissions')->get()
                    ->flatMap(fn($role) => $role->permissions)
                    ->unique('id')
                    ->values();
            }
        );
    }

    public function hasPermissionTo(string $permissionName): bool
    {
        if ($this->hasRole('admin') || $this->hasRole('super-admin')) {
            return true;
        }
        
        return $this->allPermissions()->contains('name', $permissionName);
    }

    public function hasRole(string $roleName): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains('name', $roleName);
        }
        
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function getPermissionCacheKey(): string
    {
        return "user_permissions_{$this->id}";
    }

    public function clearPermissionCache(): void
    {
        Cache::forget($this->getPermissionCacheKey());
    }

    protected static function newFactory()
    {
        return \Modules\User\Database\factories\UserFactory::new();
    }
}