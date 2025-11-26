<?php

namespace Modules\Shared\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

abstract class EloquentBaseRepository
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

    public function all($withTrashed = false)
    {
        $query = $this->query();
        if ($withTrashed && method_exists($this->model, 'withTrashed')) {
            $query->withTrashed();
        }
        return $query->latest()->get();
    }

    public function paginate($perPage = 15)
    {
        return $this->query()->latest()->paginate($perPage);
    }

    public function findById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function findByUuid($uuid): ?Model
    {
        return $this->model->where('uuid', $uuid)->firstOrFail();
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(Model $model, array $data)
    {
        $model->update($data);
        return $model;
    }

    public function delete(Model $model)
    {
        return $model->delete();
    }

    public function filter(array $filters)
    {
        $query = $this->query();

        foreach ($filters as $key => $value) {
            if (!empty($value) && $this->model->isFillable($key)) {
                $query->where($key, 'like', "%$value%");
            }
        }

        return $query->paginate(15);
    }
}
