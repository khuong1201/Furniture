<?php

declare(strict_types=1);

namespace Modules\Promotion\Domain\Repositories;

use Modules\Shared\Repositories\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PromotionRepositoryInterface extends BaseRepositoryInterface 
{
    public function filter(array $filters): LengthAwarePaginator;
}