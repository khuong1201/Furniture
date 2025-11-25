<?php

namespace Modules\Promotion\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Promotion\Domain\Models\Promotion;

class PromotionDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Promotion::factory()->count(10)->create();
    }
}
