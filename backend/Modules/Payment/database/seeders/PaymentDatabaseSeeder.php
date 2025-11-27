<?php

namespace Modules\Payment\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Payment\Domain\Models\Payment;

class PaymentDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Payment::factory()->count(10)->create();
    }
}
