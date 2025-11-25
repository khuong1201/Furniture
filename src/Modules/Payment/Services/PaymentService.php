<?php

namespace Modules\Payment\Services;

use Modules\Payment\Domain\Repositories\PaymentRepositoryInterface;

class PaymentService
{
    protected PaymentRepositoryInterface $repository;

    public function __construct(PaymentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function all()
    {
        return $this->repository->all();
    }

    public function create(array $data)
    {
        $data['uuid'] = \Str::uuid();
        return $this->repository->create($data);
    }

    public function update(string $uuid, array $data)
    {
        $payment = $this->repository->findByUuid($uuid);
        return $this->repository->update($payment, $data);
    }

    public function delete(string $uuid)
    {
        $payment = $this->repository->findByUuid($uuid);
        return $this->repository->delete($payment);
    }
}
