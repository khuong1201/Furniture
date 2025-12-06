<?php

namespace Modules\Voucher\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Voucher\Domain\Models\Voucher;

class VoucherDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Voucher giảm số tiền cố định (Fixed)
        // Value: 200,000 VND (Integer)
        Voucher::firstOrCreate(['code' => 'WELCOME2025'], [
            'name' => 'Chào thành viên mới',
            'description' => 'Giảm ngay 200k cho đơn hàng đầu tiên',
            'type' => 'fixed',
            'value' => 200000, // 200k
            'min_order_value' => 1000000,
            'max_discount_amount' => null,
            'quantity' => 1000,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'is_active' => true
        ]);

        // 2. Voucher giảm phần trăm (Percentage)
        // Value: 10 (nghĩa là 10%)
        // Max Discount: 500,000 VND (Integer)
        Voucher::firstOrCreate(['code' => 'VIPMEMBER'], [
            'name' => 'Ưu đãi VIP',
            'description' => 'Giảm 10% tối đa 500k',
            'type' => 'percentage',
            'value' => 10, // 10%
            'min_order_value' => 2000000, // Đơn từ 2 triệu
            'max_discount_amount' => 500000, // Tối đa 500k
            'quantity' => 500,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'is_active' => true
        ]);
        
        // 3. Voucher Free Ship (Logic giảm tiền ship, ở đây demo giảm thẳng vào đơn)
        Voucher::firstOrCreate(['code' => 'FREESHIP'], [
            'name' => 'Hỗ trợ phí vận chuyển',
            'type' => 'fixed',
            'value' => 50000, // 50k
            'min_order_value' => 500000, 
            'quantity' => 2000,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'is_active' => true
        ]);
    }
}