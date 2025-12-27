<?php

declare(strict_types=1);

namespace Modules\Payment\Domain\Repositories;

use Modules\Shared\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PaymentRepositoryInterface extends BaseRepositoryInterface 
{
    public function filter(array $filters): LengthAwarePaginator;
}