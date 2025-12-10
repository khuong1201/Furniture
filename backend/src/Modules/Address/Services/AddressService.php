<?php

declare(strict_types=1);

namespace Modules\Address\Services;

use Modules\Address\Domain\Repositories\AddressRepositoryInterface;
use Modules\Shared\Services\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AddressService extends BaseService
{
    public function __construct(AddressRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function listForUser(int $userId): Collection
    {
        return $this->repository->getAllByUser($userId);
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $userId = $data['user_id'];

            if (!empty($data['is_default']) && $data['is_default'] === true) {
                $this->repository->resetDefault($userId);
            }
            
            $count = $this->repository->getAllByUser($userId)->count();
            if ($count === 0) {
                $data['is_default'] = true;
            }

            return parent::create($data);
        });
    }

    public function update(string $uuid, array $data): Model
    {
        $address = $this->findByUuidOrFail($uuid);

        return DB::transaction(function () use ($address, $data) {
            if (!empty($data['is_default']) && $data['is_default'] === true) {
                $this->repository->resetDefault($address->user_id);
            }
            
            $address->update($data);
            return $address;
        });
    }
}