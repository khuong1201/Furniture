<?php

declare(strict_types=1);

namespace Modules\Shipping\Domain\Repositories;

use Modules\Shared\Repositories\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ShippingRepositoryInterface extends BaseRepositoryInterface 
{
    public function filter(array $filters): LengthAwarePaginator;
}