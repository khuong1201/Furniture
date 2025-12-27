<?php

declare(strict_types=1);

namespace Modules\Shared\Services;

use Modules\Shared\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Exception;

abstract class BaseService
{
    protected BaseRepositoryInterface $repository;

    public function __construct(BaseRepositoryInterface $repository) 
    {
        $this->repository = $repository;
    }

    public function paginate(int $perPage = 15): mixed
    {
        return $this->repository->paginate($perPage);
    }

    public function findByUuidOrFail(string $uuid): Model
    {
        $model = $this->repository->findByUuid($uuid);
        
        if (!$model) {
            throw new ModelNotFoundException("Resource with UUID $uuid not found.");
        }
        
        return $model;
    }

    public function findByUuid(string $uuid): ?Model
    {
        return $this->repository->findByUuid($uuid);
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            return $this->repository->create($data);
        });
    }

    public function update(string $uuid, array $data): Model
    {
        $model = $this->findByUuid($uuid);
        
        return DB::transaction(function () use ($model, $data) {
            return $this->repository->update($model, $data);
        });
    }

    public function delete(string $uuid): bool
    {
        $model = $this->findByUuid($uuid);
        return $this->repository->delete($model);
    }
}