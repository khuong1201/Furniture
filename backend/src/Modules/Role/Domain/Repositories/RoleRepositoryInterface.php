<?php

declare(strict_types=1);

namespace Modules\Role\Domain\Repositories;

use Modules\Shared\Contracts\BaseRepositoryInterface;
use Modules\Role\Domain\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RoleRepositoryInterface extends BaseRepositoryInterface
{
    public function findByName(string $name): ?Role;
    public function findBySlug(string $slug): ?Role; 
    public function search(array $filters, int $perPage = 15): LengthAwarePaginator;
}