<?php

namespace Modules\Shared\Contracts;

interface IRoleService
{
    public function assignRoleToUser(string $userId, string $roleId): void;
    public function removeRoleFromUser(string $userId, string $roleId): void;
}
