<?php

namespace Modules\Role\Domain\Repositories;

use Modules\Shared\Repositories\BaseRepositoryInterface;
use Modules\Role\Domain\Models\Role;

interface RoleRepositoryInterface extends BaseRepositoryInterface
{
    public function findByName(string $name): ?Role;
}