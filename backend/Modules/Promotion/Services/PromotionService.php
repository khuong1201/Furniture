<?php

declare(strict_types=1);

namespace Modules\Promotion\Services;

use Modules\Shared\Services\BaseService;
use Modules\Promotion\Domain\Repositories\PromotionRepositoryInterface;
use Modules\Promotion\Domain\Models\Promotion;
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
            // 1. Tạo Promotion
            $promotion = parent::create($data);

            // 2. Sync Products (nếu có)
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
            
            // 1. Update thông tin cơ bản
            $promotion->update($data);

            // 2. Sync Products (nếu có trường product_ids trong request)
            if (isset($data['product_ids'])) {
                $promotion->products()->sync($data['product_ids']);
            }

            return $promotion->load('products');
        });
    }
    
    /**
     * Tính toán GIÁ TRỊ GIẢM (Discount Amount) - Hỗ trợ BigInteger
     * * @param int $originalPrice Giá gốc sản phẩm (VND - BigInt)
     * @param Promotion $promotion
     * @return int Số tiền được giảm (VND - BigInt)
     */
    public function calculateDiscountAmount(int $originalPrice, Promotion $promotion): int
    {
        // Case 1: Giảm theo % (Percentage)
        if ($promotion->type === 'percentage') {
            // value = 10 -> 10%. 
            // Công thức: (price * value) / 100.
            // Sử dụng round để làm tròn chuẩn toán học trước khi cast về int
            $discount = (int) round(($originalPrice * $promotion->value) / 100);
            
            // Kiểm tra giới hạn giảm tối đa (Max Cap)
            // Lưu ý: max_discount_amount có thể null hoặc 0
            if ($promotion->max_discount_amount > 0) {
                $discount = min($discount, $promotion->max_discount_amount);
            }
            
            return $discount;
        }
        
        // Case 2: Giảm tiền mặt (Fixed)
        // Không thể giảm quá giá trị gốc của sản phẩm
        // value chính là số tiền giảm (VD: 50000)
        return min($originalPrice, $promotion->value);
    }
}