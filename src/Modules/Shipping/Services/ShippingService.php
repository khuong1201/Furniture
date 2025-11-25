<?php

namespace Modules\Shipping\Services;

use Modules\Shared\Services\BaseService;
use Modules\Shipping\Domain\Repositories\ShippingRepositoryInterface;

class ShippingService extends BaseService
{
    public function __construct(ShippingRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
}
