<?php

declare(strict_types=1);

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
            throw new ModelNotFoundException("Resource with UUID [{$uuid}] not found.");
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
        $model = $this->findByUuidOrFail($uuid);

        return DB::transaction(function () use ($model, $data) {
            $this->beforeUpdate($model, $data);
            
            $updatedModel = $this->repository->update($model, $data);
            
            $this->afterUpdate($updatedModel);
            
            return $updatedModel;
        });
    }

    public function delete(string $uuid): bool
    {
        $model = $this->findByUuidOrFail($uuid);

        return DB::transaction(function () use ($model) {
            $this->beforeDelete($model);
            
            $result = $this->repository->delete($model);
            
            $this->afterDelete($model);
            
            return $result;
        });
    }

    protected function beforeCreate(array &$data): void {}
    protected function afterCreate(Model $model): void {}
    protected function beforeUpdate(Model $model, array &$data): void {}
    protected function afterUpdate(Model $model): void {}
    protected function beforeDelete(Model $model): void {}
    protected function afterDelete(Model $model): void {}
}