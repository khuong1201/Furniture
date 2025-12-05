<?php

declare(strict_types=1);

namespace Modules\Order\Domain\Repositories;

use Modules\Shared\Repositories\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface extends BaseRepositoryInterface {
    public function countByStatus(): array;
    public function filter(array $filters): LengthAwarePaginator;
}