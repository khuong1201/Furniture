<?php

namespace Modules\Role\Domain\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Modules\User\Domain\Models\User;

class RoleUser extends Pivot
{
    protected $table = 'role_user';

    public $incrementing = false;

    public $timestamps = false; 

    protected $casts = [
        'assigned_at' => 'datetime',
        'assigned_by' => 'integer',
    ];

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}