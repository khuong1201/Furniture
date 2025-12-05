<?php

declare(strict_types=1);

namespace Modules\Shared\Contracts;

interface CartServiceInterface
{
    public function getMyCart(int $userId): array;
    public function clearCart(int $userId): void;
}