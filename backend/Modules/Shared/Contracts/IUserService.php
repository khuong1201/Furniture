<?php

namespace Modules\Shared\Contracts;

use Modules\Shared\DTO\UserDTO;

interface IUserService
{
    public function findById(string $id): ?UserDTO;
    public function findByUuid(string $uuid): ?UserDTO;
    public function hasPermission(string $userId, string $permission): bool;
    public function invalidatePermissionCache(string $userId): void;

    public function create(array $data): UserDTO;
    public function update(string $uuid, array $data): UserDTO;
    public function delete(string $uuid): void;

    // optional: hỗ trợ list/paginate nếu cần
    public function paginate(int $perPage = 15, array $filters = []): array;
}
