<?php

declare(strict_types=1);

namespace Modules\Shared\Contracts;

interface VoucherServiceInterface
{
    public function check(string $code, int $userId, float $orderTotal): array;

    public function redeem(string $code, int $userId, int $orderId, float $discountAmount): void;
}