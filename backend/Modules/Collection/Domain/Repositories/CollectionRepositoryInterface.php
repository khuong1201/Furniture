<?php

declare(strict_types=1);

namespace Modules\Collection\Domain\Repositories;

use Modules\Shared\Repositories\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CollectionRepositoryInterface extends BaseRepositoryInterface 
{
    public function filter(array $filters): LengthAwarePaginator;
}