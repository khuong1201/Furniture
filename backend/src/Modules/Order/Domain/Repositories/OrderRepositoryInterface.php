<?php

declare(strict_types=1);

namespace Modules\Order\Domain\Repositories;

use Modules\Shared\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Order\Domain\Models\Order;

interface OrderRepositoryInterface extends BaseRepositoryInterface {
    public function findForPayment(string $uuid): ?Order;
    public function countByStatus(): array;
    public function filter(array $filters): LengthAwarePaginator;
}