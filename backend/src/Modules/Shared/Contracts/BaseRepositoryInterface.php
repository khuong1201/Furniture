<?php

declare(strict_types=1);

namespace Modules\Shared\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface BaseRepositoryInterface
{
    public function all(bool $withTrashed = false): Collection;
    
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    
    public function findById(int|string $id): ?Model;
    
    public function findByUuid(string $uuid): ?Model;
    
    public function create(array $data): Model;
    
    public function updateOrCreate(array $attributes, array $values = []): Model;
    
    public function update(Model $model, array $data): Model;
    
    public function delete(Model $model): bool;
}