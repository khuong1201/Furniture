<?php

namespace Modules\Shared\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

abstract class EloquentBaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function all(bool $withTrashed = false): Collection
    {
        $query = $this->query();
        
        if ($withTrashed && method_exists($this->model, 'withTrashed')) {
            $query->withTrashed();
        }
        
        return $query->latest()->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()->latest()->paginate($perPage);
    }

    public function findById(int $id): ?Model
    {
        return $this->model->find($id); 
    }

    public function findByUuid(string $uuid): ?Model
    {
        return $this->model->where('uuid', $uuid)->first(); 
    }

    public function findByUuidAndUser(string $uuid, int $userId): ?Model
    {
        return $this->model
            ->where('uuid', $uuid)
            ->where('user_id', $userId)
            ->first();
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(Model $model, array $data): Model
    {
        $model->update($data);
        return $model->fresh();
    }

    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    public function filter(array $filters): LengthAwarePaginator
    {
        $query = $this->query();

        foreach ($filters as $key => $value) {
            if (!empty($value) && $this->model->isFillable($key)) {
                $query->where($key, 'like', "%{$value}%");
            }
        }

        return $query->paginate(15);
    }
}