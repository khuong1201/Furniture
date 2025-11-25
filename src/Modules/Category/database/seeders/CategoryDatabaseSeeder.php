<?php

namespace Modules\Category\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Category\Domain\Models\Category;

class CategoryDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Category::factory()->count(10)->create();
    }
}
