<?php

declare(strict_types=1);

namespace Modules\Promotion\Services;

use Modules\Shared\Services\BaseService;
use Modules\Promotion\Domain\Repositories\PromotionRepositoryInterface;
use Modules\Promotion\Domain\Models\Promotion;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

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
            $promotion = $this->repository->findByUuidOrFail($uuid);
            
            $promotion->update($data);

            if (isset($data['product_ids'])) {
                $promotion->products()->sync($data['product_ids']);
            }

            return $promotion->load('products');
        });
    }
    
    /**
     * Tính toán giá sau khi giảm.
     * Logic: 
     * - Percentage: Giá * (value/100), max cap bởi max_discount_amount.
     * - Fixed: Trừ thẳng value.
     */
    public function calculateDiscountAmount(float $originalPrice, Promotion $promotion): float
    {
        if ($promotion->type === 'percentage') {
            $discount = $originalPrice * ($promotion->value / 100);
            
            if ($promotion->max_discount_amount > 0) {
                $discount = min($discount, $promotion->max_discount_amount);
            }
            
            return $discount;
        }
        
        // Fixed amount
        return min($originalPrice, $promotion->value);
    }
}