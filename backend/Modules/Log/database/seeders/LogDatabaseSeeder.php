<?php

namespace Modules\Log\database\seeders;

use Illuminate\Database\Seeder;

class LogDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Log::factory()->count(20)->create();
    }
}
