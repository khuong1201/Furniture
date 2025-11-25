<?php

namespace Modules\Promotion\Infrastructure\Repositories;

use Modules\Promotion\Domain\Models\Promotion;
use Modules\Promotion\Domain\Repositories\PromotionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentPromotionRepository implements PromotionRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Promotion::with('products')->latest()->paginate($perPage);
    }

    public function findByUuid(string $uuid): ?Promotion
    {
        return Promotion::with('products')->where('uuid', $uuid)->first();
    }

    public function create(array $data): Promotion
    {
        return Promotion::create($data);
    }

    public function update(Promotion $promotion, array $data): Promotion
    {
        $promotion->update($data);
        return $promotion;
    }

    public function delete(Promotion $promotion): bool
    {
        return $promotion->delete();
    }
}
