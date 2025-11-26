<?php

namespace Modules\Shared\Services;

use Modules\Shared\Repositories\BaseRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
abstract class BaseService
{
    protected BaseRepositoryInterface $repository;

    public function __construct(BaseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function paginate()
    {
        return $this->repository->paginate();
    }

    public function findByUuid(string $uuid)
    {
        return $this->repository->findByUuid($uuid);
    }

    public function findByUuidOrFail(string $uuid)
    {
        $model = $this->repository->findByUuid($uuid);

        if (!$model) {
            throw new ModelNotFoundException("Model not found for uuid: {$uuid}");
        }

        return $model;
    }

    public function create(array $data)
    {
        return DB::transaction(fn() => $this->repository->create($data));
    }

    public function update(string $uuid, array $data)
    {
        $model = $this->repository->findByUuid($uuid);
        return DB::transaction(fn() => $this->repository->update($model, $data));
    }

    public function delete(string $uuid)
    {
        $model = $this->repository->findByUuid($uuid);
        return DB::transaction(fn() => $this->repository->delete($model));
    }
}
