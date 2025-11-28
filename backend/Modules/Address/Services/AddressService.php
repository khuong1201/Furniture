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

    public function listForUser(int $userId)
    {
        return $this->repository->getAllByUser($userId);
    }

    public function createForUser(int $userId, array $data)
    {
        $data['uuid'] = Str::uuid();
        $data['user_id'] = $userId;
        return $this->repository->create($data);
    }

    public function updateForUser(string $uuid, array $data, int $userId)
    {
        $address = $this->repository->findByUuidAndUser($uuid, $userId);
        return $this->repository->update($address, $data);
    }

    public function deleteForUser(string $uuid, int $userId)
    {
        $address = $this->repository->findByUuidAndUser($uuid, $userId);
        return $this->repository->delete($address);
    }
}