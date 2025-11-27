<?php

namespace Modules\Promotion\Services;

use Modules\Promotion\Domain\Repositories\PromotionRepositoryInterface;
use Modules\Promotion\Domain\Repositories\PromotionProductRepositoryInterface;

class PromotionService
{
    public function __construct(
        protected PromotionRepositoryInterface $promotionRepo,
        protected PromotionProductRepositoryInterface $productRepo
    ) {}

    public function paginate(int $perPage = 15)
    {
        return $this->promotionRepo->paginate($perPage);
    }

    public function store(array $data)
    {
        $promotion = $this->promotionRepo->create($data);
        if (!empty($data['product_ids'])) {
            $this->productRepo->attachProducts($promotion->id, $data['product_ids']);
        }
        return $promotion->load('products');
    }

    public function update(string $uuid, array $data)
    {
        $promotion = $this->promotionRepo->findByUuid($uuid);
        if (!$promotion) abort(404, 'Promotion not found');

        $this->promotionRepo->update($promotion, $data);

        if (isset($data['product_ids'])) {
            $this->productRepo->detachProducts($promotion->id, $promotion->products->pluck('id')->toArray());
            $this->productRepo->attachProducts($promotion->id, $data['product_ids']);
        }

        return $promotion->fresh('products');
    }

    public function delete(string $uuid)
    {
        $promotion = $this->promotionRepo->findByUuid($uuid);
        if (!$promotion) abort(404, 'Promotion not found');
        $this->promotionRepo->delete($promotion);
    }
}
