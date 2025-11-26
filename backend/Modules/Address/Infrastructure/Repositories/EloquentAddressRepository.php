<?php

namespace Modules\Address\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Address\Domain\Repositories\AddressRepositoryInterface;
use Modules\Address\Domain\Models\Address;

class EloquentAddressRepository extends EloquentBaseRepository implements AddressRepositoryInterface
{
    public function __construct(Address $model)
    {
        parent::__construct($model);
    }

    public function getAllByUser($userId)
    {
        return $this->model->where('user_id', $userId)->get();
    }

}
