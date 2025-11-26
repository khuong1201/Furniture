<?php

namespace Modules\Shared\Contracts;

interface IPermissionRepository
{
    public function getAll(): array;
    public function getByRoleIds(array $roleIds): array;
}
