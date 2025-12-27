<?php

declare(strict_types=1);

namespace Modules\Category\Domain\Repositories;

use Modules\Shared\Contracts\BaseRepositoryInterface;
use Modules\Category\Domain\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function getTree(): Collection;
    public function filter(array $filters): LengthAwarePaginator;
    public function findBySlug(string $slug): ?Category; 
}