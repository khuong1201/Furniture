<?php

namespace Modules\Order\Domain\Repositories;


use Modules\Shared\Repositories\BaseRepositoryInterface;
use Modules\Order\Domain\Models\Order;

interface OrderRepositoryInterface extends BaseRepositoryInterface {
    public function countByStatus();
}