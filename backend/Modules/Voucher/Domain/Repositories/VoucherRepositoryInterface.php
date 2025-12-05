<?php

declare(strict_types=1);

namespace Modules\Voucher\Domain\Repositories;

use Modules\Shared\Repositories\BaseRepositoryInterface;
use Modules\Voucher\Domain\Models\Voucher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface VoucherRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCode(string $code): ?Voucher;
    public function filter(array $filters): LengthAwarePaginator;
}