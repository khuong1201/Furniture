<?php

namespace Modules\Currency\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Currency\Domain\Models\Currency;

class CurrencyDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Currency::firstOrCreate(['code' => 'VND'], [
            'name' => 'Vietnam Dong',
            'symbol' => 'VND',
            'exchange_rate' => 1,
            'is_default' => true,
            'is_active' => true
        ]);

        Currency::firstOrCreate(['code' => 'USD'], [
            'name' => 'US Dollar',
            'symbol' => 'USD',
            'exchange_rate' => 0.000041, 
            'is_default' => false,
            'is_active' => true
        ]);
    }
}