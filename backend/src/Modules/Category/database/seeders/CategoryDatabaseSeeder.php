<?php

namespace Modules\Category\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Category\Domain\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; // Vẫn cần Str để slug thủ công cho data cố định

class CategoryDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Category::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $structure = [
            'Living Room' => ['Sofa', 'Coffee Table', 'TV Cabinet', 'Lighting'],
            'Bedroom'     => ['Bed', 'Wardrobe', 'Dressing Table', 'Mattress'],
            'Dining'      => ['Dining Table', 'Dining Chairs', 'Kitchen Cabinet'],
            'Office'      => ['Work Desk', 'Office Chair', 'Filing Cabinet'],
            'Decor'       => ['Wall Art', 'Indoor Plants', 'Rugs'],
        ];

        foreach ($structure as $parentName => $children) {
            // Tạo Parent Category bằng Factory
            $parent = Category::factory()->create([
                'name'  => $parentName,
                'slug'  => Str::slug($parentName),
                'image' => "https://placehold.co/300x300?text=" . urlencode($parentName),
            ]);

            // Tạo Children Categories bằng Factory
            foreach ($children as $childName) {
                Category::factory()->create([
                    'name'      => $childName,
                    'slug'      => Str::slug($childName),
                    'parent_id' => $parent->id,
                    'image'     => "https://placehold.co/300x300?text=" . urlencode($childName),
                ]);
            }
        }
    }
}