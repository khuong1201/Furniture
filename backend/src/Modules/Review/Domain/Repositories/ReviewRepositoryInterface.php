<?php

declare(strict_types=1);

namespace Modules\Review\Domain\Repositories;

use Modules\Shared\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReviewRepositoryInterface extends BaseRepositoryInterface 
{
    public function filter(array $filters): LengthAwarePaginator;

    public function getStats(int $productId): array;
    
    public function getRatingCounts(int $productId): array;
}