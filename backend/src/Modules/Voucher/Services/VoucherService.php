<?php

declare(strict_types=1);

namespace Modules\Voucher\Services;

use Modules\Shared\Services\BaseService;
use Modules\Voucher\Domain\Repositories\VoucherRepositoryInterface;
use Modules\Voucher\Domain\Models\Voucher;
use Illuminate\Validation\ValidationException;

class VoucherService extends BaseService
{
    public function __construct(VoucherRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Kiểm tra và tính toán giá trị giảm giá cho giỏ hàng.
     * * @return array{voucher: Voucher, discount_amount: float}
     */
    public function applyVoucher(string $code, float $cartTotal, int $userId): array
    {
        $voucher = $this->repository->findByCode($code);

        if (!$voucher) {
            throw ValidationException::withMessages(['code' => 'Mã giảm giá không tồn tại.']);
        }

        if (!$voucher->isValid()) {
            throw ValidationException::withMessages(['code' => 'Mã giảm giá đã hết hạn hoặc hết lượt sử dụng.']);
        }

        if ($voucher->min_order_value && $cartTotal < $voucher->min_order_value) {
            throw ValidationException::withMessages(['code' => "Đơn hàng phải từ " . number_format($voucher->min_order_value) . "đ để sử dụng mã này."]);
        }

        $userUsageCount = $voucher->usages()->where('user_id', $userId)->count();
        if ($userUsageCount >= $voucher->limit_per_user) {
            throw ValidationException::withMessages(['code' => 'Bạn đã hết lượt sử dụng mã này.']);
        }

        $discount = 0;
        if ($voucher->type === 'fixed') {
            $discount = $voucher->value;
        } else {
            $discount = $cartTotal * ($voucher->value / 100);
            if ($voucher->max_discount_amount > 0) {
                $discount = min($discount, $voucher->max_discount_amount);
            }
        }

        // Discount không được vượt quá tổng đơn hàng
        $discount = min($discount, $cartTotal);

        return [
            'voucher' => $voucher,
            'discount_amount' => (float) $discount
        ];
    }
}