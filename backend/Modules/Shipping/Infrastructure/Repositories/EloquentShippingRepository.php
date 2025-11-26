<?php

namespace Modules\Shipping\Infrastructure\Repositories;

use Modules\Shared\Repositories\BaseRepository;
use Modules\Shipping\Domain\Repositories\ShippingRepositoryInterface;
use Modules\Shipping\Domain\Models\Shipping;

class EloquentShippingRepository extends BaseRepository implements ShippingRepositoryInterface
{
    public function __construct(Shipping $model)
    {
        parent::__construct($model);
    }

    public function update(\Illuminate\Database\Eloquent\Model $model, array $data)
    {
        return parent::update($model, $data);
    }

    public function delete(\Illuminate\Database\Eloquent\Model $model)
    {
        return parent::delete($model);
    }

    public function findByUuid($uuid)
    {
        return parent::findByUuid($uuid);
    }

    public function create(array $data)
    {
        return parent::create($data);
    }

    public function all($withTrashed = false)
    {
        return parent::all($withTrashed);
    }

    public function paginate($perPage = 15)
    {
        return parent::paginate($perPage);
    }
}