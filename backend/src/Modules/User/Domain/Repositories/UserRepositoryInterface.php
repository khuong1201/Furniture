<?php

declare(strict_types=1);

namespace Modules\User\Domain\Repositories;

use Modules\Shared\Repositories\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function filter(array $filters): LengthAwarePaginator;
}