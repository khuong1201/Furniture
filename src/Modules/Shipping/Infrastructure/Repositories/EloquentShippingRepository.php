<?php

namespace Modules\Shipping\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Shipping\Domain\Repositories\ShippingRepositoryInterface;
use Modules\Shipping\Domain\Models\Shipping;

class EloquentShippingRepository extends EloquentBaseRepository implements ShippingRepositoryInterface
{
    public function __construct(Shipping $model)
    {
        parent::__construct($model);
    }
}