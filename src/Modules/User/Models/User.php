<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class User extends Model
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'uuid', 'name', 'email', 'password', 'is_active', 'avatar_url', 'is_deleted',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    // Relationships
    public function addresses() {
        return $this->hasMany(Address::class);
    }

    public function orders() {
        return $this->hasMany(Order::class);
    }

    public function logs() {
        return $this->hasMany(Log::class);
    }

    public function roles()
    {
        return $this->belongsToMany(\Modules\Role\Models\Role::class);
    }

    // Scopes
    public function scopeActive($query) {
        return $query->where('is_deleted', false);
    }

    // Role check (case-insensitive)
    public function hasRole($role)
    {
        return $this->roles()
            ->whereRaw('LOWER(name) = ?', [strtolower($role)])
            ->exists();
    }

    // Permission check (case-insensitive)
    public function hasPermission($permission)
    {
        return $this->roles()
            ->whereHas('permissions', fn($q) =>
                $q->whereRaw('LOWER(name) = ?', [strtolower($permission)])
            )->exists();
    }

    public static function newFactory()
    {
        return \Modules\User\Database\Factories\UserFactory::new();
    }
}