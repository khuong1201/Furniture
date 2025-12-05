<?php

namespace Modules\Voucher\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Voucher\Domain\Models\Voucher;

class VoucherDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Voucher::firstOrCreate(
            ['code' => 'WELCOME2025'],
            [
                'name' => 'New Member Welcome Gift',
                'type' => 'fixed',
                'value' => 10.00, 
                'min_order_value' => 50.00,
                'quantity' => 1000,
                'start_date' => now(),
                'end_date' => now()->addYear(),
                'is_active' => true
            ]
        );

        Voucher::firstOrCreate(
            ['code' => 'FURNITURE20'],
            [
                'name' => 'Furniture Seasonal Sale 20%',
                'type' => 'percentage',
                'value' => 20, // 20% off
                'max_discount_amount' => 100.00,
                'quantity' => 500,
                'start_date' => now(),
                'end_date' => now()->addMonth(),
                'is_active' => true
            ]
        );
    }
}