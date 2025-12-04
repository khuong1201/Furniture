<?php

namespace Modules\Shared\Services;

use Modules\Shared\Repositories\BaseRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

abstract class BaseService
{
    protected BaseRepositoryInterface $repository;

    public function __construct(BaseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getRepository(): BaseRepositoryInterface
    {
        return $this->repository;
    }

    public function getAll(bool $withTrashed = false): Collection
    {
        return $this->repository->all($withTrashed);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function findByUuid(string $uuid): ?Model
    {
        return $this->repository->findByUuid($uuid);
    }

    public function findByUuidOrFail(string $uuid): Model
    {
        $model = $this->repository->findByUuid($uuid);

        if (!$model) {
            throw new ModelNotFoundException(
                sprintf('Model with UUID [%s] not found.', $uuid)
            );
        }

        return $model;
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $this->beforeCreate($data);
            $model = $this->repository->create($data);
            $this->afterCreate($model);

            return $model;
        });
    }

    public function update(string $uuid, array $data): Model
    {
        return DB::transaction(function () use ($uuid, $data) {
            $model = $this->findByUuidOrFail($uuid);

            $this->beforeUpdate($model, $data);
            $updated = $this->repository->update($model, $data);
            $this->afterUpdate($updated);

            return $updated;
        });
    }

    public function delete(string $uuid): bool
    {
        return DB::transaction(function () use ($uuid) {
            $model = $this->findByUuidOrFail($uuid);

            $this->beforeDelete($model);
            $result = $this->repository->delete($model);
            $this->afterDelete($model);

            return $result;
        });
    }

    protected function beforeCreate(array &$data): void
    {
    }
    protected function afterCreate(Model $model): void
    {
    }
    protected function beforeUpdate(Model $model, array &$data): void
    {
    }
    protected function afterUpdate(Model $model): void
    {
    }
    protected function beforeDelete(Model $model): void
    {
    }
    protected function afterDelete(Model $model): void
    {
    }
}