<?php

namespace Modules\Promotion\Domain\Repositories;

use Modules\Promotion\Domain\Models\Promotion;
use Illuminate\Pagination\LengthAwarePaginator;

interface PromotionRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function findByUuid(string $uuid): ?Promotion;
    public function create(array $data): Promotion;
    public function update(Promotion $promotion, array $data): Promotion;
    public function delete(Promotion $promotion): bool;
}
