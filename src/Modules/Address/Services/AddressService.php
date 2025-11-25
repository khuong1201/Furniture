<?php

namespace Modules\Address\Services;

use Illuminate\Support\Str;
use Modules\Address\Domain\Repositories\AddressRepositoryInterface;
use Modules\Shared\Services\BaseService;

class AddressService extends BaseService
{
    public function __construct(AddressRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function listForUser($userId)
    {
        return $this->repository->getAllByUser($userId);
    }

    public function createForUser($userId, array $data)
    {
        $data['uuid'] = Str::uuid();
        $data['user_id'] = $userId;
        return $this->repository->create($data);
    }

    public function update(string $uuid, array $data)
    {
        $address = $this->repository->findByUuid($uuid);
        return $this->repository->update($address, $data);
    }

    public function delete(string $uuid)
    {
        $address = $this->repository->findByUuid($uuid);
        return $this->repository->delete($address); 
    }
}
