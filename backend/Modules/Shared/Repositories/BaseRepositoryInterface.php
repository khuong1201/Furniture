<?php

namespace Modules\Shared\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    public function all(bool $withTrashed = false): Collection;
    
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    
    public function findById(int $id): ?Model;
    
    public function findByUuid(string $uuid): ?Model;
    
    public function findByUuidAndUser(string $uuid, int $userId): ?Model;
    
    public function create(array $data): Model;
    
    public function update(Model $model, array $data): Model;
    
    public function delete(Model $model): bool;
}