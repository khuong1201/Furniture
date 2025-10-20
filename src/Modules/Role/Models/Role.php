<?php

namespace Modules\Role\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'label'];

    public function permissions()
    {
        return $this->belongsToMany(\Modules\Permission\Models\Permission::class);
    }

    public function users()
    {
        return $this->belongsToMany(\Modules\User\Models\User::class);
    }
}

