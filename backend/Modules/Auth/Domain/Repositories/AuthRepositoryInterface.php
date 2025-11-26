<?php

namespace Modules\Auth\Domain\Repositories;

use Modules\Shared\Repositories\BaseRepositoryInterface;
use Modules\User\Domain\Models\User;

interface AuthRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email): ?User;
}
