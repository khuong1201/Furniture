<?php

namespace Modules\Shared\Repositories;

use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    public function all($withTrashed = false);
    public function paginate($perPage = 15);
    public function findById($id);
    public function findByUuid($uuid): ?Model;
    public function create(array $data): ?Model;
    public function update(Model $model, array $data);
    public function delete(Model $model);
}
