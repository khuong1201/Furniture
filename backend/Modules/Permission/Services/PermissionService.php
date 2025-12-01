<?php

namespace Modules\Permission\Services;

use Modules\Shared\Services\BaseService;
use Modules\Shared\Services\CacheService;
use Modules\Permission\Domain\Repositories\PermissionRepositoryInterface;

class PermissionService extends BaseService
{
    protected const CACHE_TTL = 86400; 

    public function __construct(
        PermissionRepositoryInterface $repo,
        protected CacheService $cacheService
    ) {
        parent::__construct($repo);
    }

    public function getUserPermissions(int $userId): array
    {
        $cacheKey = "user_permissions_{$userId}";

        return $this->cacheService->remember($cacheKey, function () use ($userId) {
            return $this->repository->getPermissionsByUserId($userId);
        }, self::CACHE_TTL);
    }

    public function hasPermission(int $userId, string $permission): bool
    {
        $permissions = $this->getUserPermissions($userId);
        return in_array($permission, $permissions);
    }
}