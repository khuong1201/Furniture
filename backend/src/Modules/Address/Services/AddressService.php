<?php

declare(strict_types=1);

namespace Modules\Address\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Address\Domain\Repositories\AddressRepositoryInterface;
use Modules\Shared\Exceptions\BusinessException;
use Modules\Shared\Services\BaseService;

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

            $count = $this->repository->getAllByUser($userId)->count();
            if ($count === 0) {
                $data['is_default'] = true;
            }

            if ($count >= 10) {
                throw new BusinessException(400033);
            }

            if (!empty($data['is_default']) && $data['is_default'] === true) {
                $this->repository->resetDefault($userId);
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

            elseif (isset($data['is_default']) && $data['is_default'] === false && $address->is_default) {

                 unset($data['is_default']);
            }

            $address->update($data);
            return $address;
        });
    }

    public function delete(string $uuid): bool
    {
        $address = $this->findByUuidOrFail($uuid);


        if ($address->is_default) {

            $count = $this->repository->getAllByUser($address->user_id)->count();
            if ($count > 1) {
                throw new BusinessException(403032); 
            }
        }

        return parent::delete($uuid);
    }
}