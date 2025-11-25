<?php

namespace Modules\Order\Domain\Repositories;

use Modules\Shared\Repositories\BaseRepositoryInterface;

interface OrderRepositoryInterface extends BaseRepositoryInterface{
    public function findByUuidOrFail(string $uuid);
}