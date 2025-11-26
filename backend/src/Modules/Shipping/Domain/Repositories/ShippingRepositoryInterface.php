<?php
namespace Modules\Shipping\Domain\Repositories;

use Modules\Shipping\Domain\Models\Shipping;
use Illuminate\Database\Eloquent\Model;
interface ShippingRepositoryInterface
{
    public function all($withTrashed = false);
    public function paginate($perPage = 15);
    public function findByUuid($uuid);
    public function create(array $data);
    public function update(Model $model, array $data);
    public function delete(Model $model);
}


