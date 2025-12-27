<?php

declare(strict_types=1);

namespace Modules\Voucher\Services;

use Modules\Shared\Services\BaseService;
use Modules\Voucher\Domain\Repositories\VoucherRepositoryInterface;
use Modules\Voucher\Domain\Models\Voucher;
use Modules\Voucher\Domain\Models\VoucherUsage;
use Modules\Shared\Contracts\VoucherServiceInterface;
use Modules\Shared\Exceptions\BusinessException;
use Illuminate\Support\Facades\DB;

class VoucherService extends BaseService implements VoucherServiceInterface
{
    public function __construct(VoucherRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function check(string $code, int $userId, float $orderTotal): array
    {
        $voucher = $this->repository->findByCode($code);

        if (!$voucher) {
            throw new BusinessException(404210);
        }

        if (!$this->isValidBasic($voucher)) {
            throw new BusinessException(400211, 'Mã giảm giá đã hết hạn hoặc ngưng hoạt động.');
        }

        if ($voucher->min_order_value && $orderTotal < $voucher->min_order_value) {
            throw new BusinessException(400212, "Đơn hàng phải từ " . number_format((float)$voucher->min_order_value) . "đ để sử dụng.");
        }

        $usedCount = VoucherUsage::where('voucher_id', $voucher->id)
            ->where('user_id', $userId)
            ->count();
            
        if ($usedCount >= $voucher->limit_per_user) {
            throw new BusinessException(400211, 'Bạn đã hết lượt sử dụng mã này.');
        }

        $discount = 0;
        if ($voucher->type === 'fixed') {
            $discount = (float) $voucher->value;
        } else {
            $discount = $orderTotal * ($voucher->value / 100);
            if ($voucher->max_discount_amount > 0) {
                $discount = min($discount, (float) $voucher->max_discount_amount);
            }
        }

        $discount = min($discount, $orderTotal);

        return [
            'code' => $voucher->code,
            'discount_amount' => $discount,
            'voucher_id' => $voucher->id
        ];
    }

    public function redeem(string $code, int $userId, int $orderId, float $discountAmount): void
    {
        $voucher = $this->repository->findByCode($code);
        if (!$voucher) return;

        DB::transaction(function () use ($voucher, $userId, $orderId, $discountAmount) {
            $voucher->increment('used_count');

            VoucherUsage::create([
                'voucher_id' => $voucher->id,
                'user_id' => $userId,
                'order_id' => $orderId,
                'discount_amount' => $discountAmount,
                'used_at' => now()
            ]);
        });
    }

    protected function isValidBasic(Voucher $voucher): bool
    {
        if (!$voucher->is_active) return false;

        if ($voucher->quantity > 0 && $voucher->used_count >= $voucher->quantity) {
            return false;
        }

        $now = now();
        if ($voucher->start_date && $voucher->start_date > $now) return false;
        if ($voucher->end_date && $voucher->end_date < $now) return false;

        return true;
    }
}