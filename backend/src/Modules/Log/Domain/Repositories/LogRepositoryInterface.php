<?php

declare(strict_types=1);

namespace Modules\Log\Domain\Repositories;

use Modules\Shared\Repositories\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface LogRepositoryInterface extends BaseRepositoryInterface
{
    public function getLogsByFilters(array $filters, int $perPage = 20): LengthAwarePaginator;
}