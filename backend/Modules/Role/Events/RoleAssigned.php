<?php

declare(strict_types=1);

namespace Modules\Role\Events;

use Modules\User\Domain\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleAssigned
{
    use Dispatchable, SerializesModels;

    public function __construct(public User $user) 
    {
    }
}