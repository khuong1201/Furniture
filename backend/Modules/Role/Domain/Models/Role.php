<?php

namespace Modules\Role\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Permission\Domain\Models\Permission;
use Modules\User\Domain\Models\User;
use Modules\Role\Database\Factories\RoleFactory;
class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user');
    }
    protected static function newFactory()
    {
        return RoleFactory::new();
    }
}