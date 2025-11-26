<?php

namespace Modules\Payment\Domain\Repositories;

use Modules\Payment\Domain\Models\Payment;

interface PaymentRepositoryInterface
{
    public function all();
    public function findByUuid(string $uuid): ?Payment;
    public function create(array $data): Payment;
    public function update(Payment $payment, array $data): Payment;
    public function delete(Payment $payment): bool;
}
