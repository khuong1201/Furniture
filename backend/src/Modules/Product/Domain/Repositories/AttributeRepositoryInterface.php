<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator; 
use Modules\Shared\Contracts\BaseRepositoryInterface;

interface AttributeRepositoryInterface extends BaseRepositoryInterface 
{
    public function filter(array $filters): LengthAwarePaginator;
}