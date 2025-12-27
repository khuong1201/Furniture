<?php

declare(strict_types=1);

namespace Modules\Warehouse\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Exceptions\BusinessException;
use Modules\Shared\Services\BaseService;
use Modules\Warehouse\Domain\Repositories\WarehouseRepositoryInterface;

class WarehouseService extends BaseService
{
    public function __construct(WarehouseRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function create(array $data): Model
    {
        if ($this->repository->findByName($data['name'])) {
            throw new BusinessException(409221);
        }

        return parent::create($data);
    }

    public function update(string $uuid, array $data): Model
    {
        $warehouse = $this->findByUuidOrFail($uuid);

        if (isset($data['name'])) {
            $existing = $this->repository->findByName($data['name']);
            if ($existing && $existing->id !== $warehouse->id) {
                throw new BusinessException(409221);
            }
        }

        return parent::update($uuid, $data);
    }

    public function delete(string $uuid): bool
    {
        $warehouse = $this->findByUuidOrFail($uuid);

        if ($this->repository->hasStock($warehouse->id)) {
            throw new BusinessException(409222); 
        }

        return parent::delete($uuid);
    }

    public function paginate(int $perPage = 15, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $filters['per_page'] = $perPage;
        return $this->repository->filter($filters);
    }

    public function getWarehouseStats(string $uuid): array
    {
        return $this->repository->getDashboardStats($uuid);
    }
    
}