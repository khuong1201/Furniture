<?php

namespace Modules\Promotion\Services;

use Modules\Shared\Services\BaseService;
use Modules\Promotion\Domain\Repositories\PromotionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class PromotionService extends BaseService
{
    public function __construct(PromotionRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $promotion = parent::create($data);

            if (!empty($data['product_ids'])) {
                $promotion->products()->sync($data['product_ids']);
            }

            return $promotion->load('products');
        });
    }

    public function update(string $uuid, array $data): Model
    {
        return DB::transaction(function () use ($uuid, $data) {
            $promotion = $this->repository->findByUuid($uuid);
            
            $promotion->update($data);

            if (isset($data['product_ids'])) {
                $promotion->products()->sync($data['product_ids']);
            }

            return $promotion->load('products');
        });
    }
    
    public function calculateDiscount($originalPrice, $promotion)
    {
        if ($promotion->type === 'percentage') {
            return $originalPrice * ($promotion->value / 100);
        }
        return min($originalPrice, $promotion->value);
    }
}