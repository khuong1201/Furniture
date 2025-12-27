<?php

declare(strict_types=1);

namespace Modules\User\Domain\Repositories;

use Modules\User\Domain\Models\User;
use Modules\Shared\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function filter(array $filters): LengthAwarePaginator;
    public function findByEmail(string $email): ?User;
}