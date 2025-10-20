<?php

namespace Modules\Permission\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'label'];

    public function roles()
    {
        return $this->belongsToMany(\Modules\Role\Models\Role::class);
    }
}

