<?php

namespace Modules\Permission\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Modules\Role\Domain\Models\Role;
use Modules\Shared\Traits\Loggable;

class Permission extends Model
{
    use HasFactory, Loggable;

    protected $fillable = ['uuid', 'name', 'description', 'module'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role');
    }
}