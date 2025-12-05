<?php

declare(strict_types=1);

namespace Modules\Cart\Domain\Repositories;

use Modules\Shared\Repositories\BaseRepositoryInterface;
use Modules\Cart\Domain\Models\Cart;

interface CartRepositoryInterface extends BaseRepositoryInterface
{
    public function findByUser(int $userId): ?Cart;
    public function firstOrCreateByUser(int $userId): Cart;
}