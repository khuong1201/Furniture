<?php

namespace Modules\Review\Domain\Repositories;

use Modules\Review\Domain\Models\Review;
use Illuminate\Pagination\LengthAwarePaginator;

interface ReviewRepositoryInterface
{
    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator;
    public function create(array $data): Review;
    public function update(Review $review, array $data): Review;
    public function delete(Review $review): bool;
    public function findByUuid(string $uuid): ?Review;
}
