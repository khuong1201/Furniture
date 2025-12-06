<?php

declare(strict_types=1);

namespace Modules\Category\Domain\Repositories;

use Modules\Shared\Repositories\BaseRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function getTree(bool $includeInactive = false): Collection;
    public function filter(array $filters): LengthAwarePaginator;
}