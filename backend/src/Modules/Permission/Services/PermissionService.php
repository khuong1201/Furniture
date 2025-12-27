<?php

declare(strict_types=1);

namespace Modules\Permission\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache; 
use Modules\Permission\Domain\Repositories\PermissionRepositoryInterface;
use Modules\Shared\Exceptions\BusinessException;
use Modules\Shared\Services\BaseService;

class PermissionService extends BaseService
{
    protected const CACHE_TTL = 86400;

    public function __construct(PermissionRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function create(array $data): Model
    {
        if ($this->repository->findByName($data['name'])) {
            throw new BusinessException(409151, "Permission '{$data['name']}' already exists");
        }

        return parent::create($data);
    }

    public function update(string $uuid, array $data): Model
    {
        $permission = $this->findByUuidOrFail($uuid);

        if (isset($data['name'])) {
            $existing = $this->repository->findByName($data['name']);
            if ($existing && $existing->id !== $permission->id) {
                throw new BusinessException(409151);
            }
        }

        $updated = parent::update($uuid, $data);
        
        return $updated;
    }

    public function delete(string $uuid): bool
    {
        $permission = $this->findByUuidOrFail($uuid);

        if ($permission->roles()->exists()) {
            throw new BusinessException(403152);
        }

        return parent::delete($uuid);
    }

    public function clearUserPermissionCache(int $userId): void
    {
        Cache::forget("user_permissions_{$userId}");
    }

    public function getUserPermissions(int $userId): array
    {
        $cacheKey = "user_permissions_{$userId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId) {
            return $this->repository->getPermissionsByUserId($userId);
        });
    }

    public function hasPermission(int $userId, string $permissionName): bool
    {
        $permissions = $this->getUserPermissions($userId);
        return in_array($permissionName, $permissions, true);
    }
}