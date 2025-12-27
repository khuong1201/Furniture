<?php

namespace Modules\Voucher\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Voucher\Domain\Models\Voucher;
use Illuminate\Support\Str;

class VoucherDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $vouchers = [
            [
                'code' => 'WELCOME50',
                'name' => 'Chào bạn mới',
                'type' => 'fixed',
                'value' => 50000,
                'min_order_value' => 200000,
                'quantity' => 1000,
                'limit_per_user' => 1,
            ],
            [
                'code' => 'FREESHIP',
                'name' => 'Miễn phí vận chuyển',
                'type' => 'fixed',
                'value' => 30000,
                'min_order_value' => 300000,
                'quantity' => 5000,
                'limit_per_user' => 10,
            ],
            [
                'code' => 'VIP10',
                'name' => 'Giảm 10% tối đa 100k',
                'type' => 'percentage',
                'value' => 10,
                'max_discount_amount' => 100000,
                'min_order_value' => 500000,
                'quantity' => 200,
                'limit_per_user' => 1,
            ],
            [
                'code' => 'SOFA500',
                'name' => 'Giảm 500k cho Sofa',
                'type' => 'fixed',
                'value' => 500000,
                'min_order_value' => 5000000,
                'quantity' => 50,
                'limit_per_user' => 1,
            ],
            [
                'code' => 'TET2025',
                'name' => 'Lì xì đầu năm',
                'type' => 'fixed',
                'value' => 68000,
                'min_order_value' => 0,
                'quantity' => 100,
                'limit_per_user' => 1,
                'start_date' => now()->addMonths(1),
            ],
            [
                'code' => 'EXPIRED_CODE',
                'name' => 'Mã đã hết hạn',
                'type' => 'percentage',
                'value' => 50,
                'min_order_value' => 0,
                'quantity' => 100,
                'limit_per_user' => 1,
                'start_date' => now()->subMonths(2),
                'end_date' => now()->subMonths(1),
            ],
        ];

        foreach ($vouchers as $v) {
            Voucher::firstOrCreate(['code' => $v['code']], [
                'uuid' => (string) Str::uuid(),
                'name' => $v['name'],
                'type' => $v['type'],
                'value' => $v['value'],
                'min_order_value' => $v['min_order_value'],
                'max_discount_amount' => $v['max_discount_amount'] ?? null,
                'quantity' => $v['quantity'],
                
                // --- FIX LỖI Ở ĐÂY (Đổi used_quantity -> used_count) ---
                'used_count' => rand(0, floor($v['quantity'] / 2)), 
                
                'limit_per_user' => $v['limit_per_user'],
                'start_date' => $v['start_date'] ?? now(),
                'end_date' => $v['end_date'] ?? now()->addMonths(3),
                'is_active' => true
            ]);
        }
    }
}