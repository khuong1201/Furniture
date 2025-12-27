<?php

declare(strict_types=1);

namespace Modules\Brand\Domain\Repositories;

use Modules\Shared\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BrandRepositoryInterface extends BaseRepositoryInterface
{
    public function filter(array $filters): LengthAwarePaginator;
}