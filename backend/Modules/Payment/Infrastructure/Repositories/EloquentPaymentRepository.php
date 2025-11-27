<?php

namespace Modules\Payment\Infrastructure\Repositories;

use Modules\Payment\Domain\Models\Payment;
use Modules\Payment\Domain\Repositories\PaymentRepositoryInterface;

class EloquentPaymentRepository implements PaymentRepositoryInterface
{
    public function all()
    {
        return Payment::all();
    }

    public function findByUuid(string $uuid): ?Payment
    {
        return Payment::where('uuid', $uuid)->first();
    }

    public function create(array $data): Payment
    {
        return Payment::create($data);
    }

    public function update(Payment $payment, array $data): Payment
    {
        $payment->update($data);
        return $payment;
    }

    public function delete(Payment $payment): bool
    {
        return $payment->delete();
    }
}
