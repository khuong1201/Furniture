<?php

namespace Modules\Currency\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Currency\Domain\Models\Currency;

class CurrencyDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1 VND = 1 VND
        Currency::firstOrCreate(['code' => 'VND'], [
            'name' => 'Vietnam Dong',
            'symbol' => '₫',
            'exchange_rate' => 1.00000000,
            'is_default' => true,
            'is_active' => true
        ]);

        // 1 VND = 0.000039 USD (Giả định 1 USD = 25,600 VND)
        Currency::firstOrCreate(['code' => 'USD'], [
            'name' => 'US Dollar',
            'symbol' => '$',
            'exchange_rate' => 0.00003906, 
            'is_default' => false,
            'is_active' => true
        ]);
    }
}