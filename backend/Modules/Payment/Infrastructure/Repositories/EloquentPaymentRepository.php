<?php

namespace Modules\Payment\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Payment\Domain\Models\Payment;
use Modules\Payment\Domain\Repositories\PaymentRepositoryInterface;

class EloquentPaymentRepository extends EloquentBaseRepository implements PaymentRepositoryInterface {
    public function __construct(Payment $model)
    {
        parent::__construct($model);
    }
}