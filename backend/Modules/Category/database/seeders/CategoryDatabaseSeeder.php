<?php

namespace Modules\Category\database\seeders;

use Illuminate\Database\Seeder;
use Modules\Category\Domain\Models\Category;
use Illuminate\Support\Str;

class CategoryDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Xóa dữ liệu cũ
        Category::query()->forceDelete();

        $categories = [
            'Living Room' => ['Sofas & Armchairs', 'Coffee Tables', 'TV Stands', 'Cabinets & Storage'],
            'Bedroom' => ['Beds', 'Wardrobes', 'Nightstands', 'Mattresses', 'Dressers'],
            'Dining & Kitchen' => ['Dining Tables', 'Dining Chairs', 'Bar Stools', 'Kitchen Islands'],
            'Office' => ['Office Desks', 'Office Chairs', 'Bookshelves'],
            'Decor & Lighting' => ['Chandeliers', 'Floor Lamps', 'Rugs', 'Mirrors', 'Wall Art'],
            'Smart Furniture' => ['Sofa Beds', 'Extendable Tables', 'Smart Beds']
        ];

        foreach ($categories as $parentName => $children) {

            // Tạo category cha
            $parent = Category::create([
                'uuid' => (string) Str::uuid(),
                'name' => $parentName,
                'slug' => Str::slug($parentName),
                'description' => "Premium furniture for " . strtolower($parentName),
                'parent_id' => null,
                'is_active' => true
            ]);

            // Tạo category con
            foreach ($children as $childName) {
                Category::create([
                    'uuid' => (string) Str::uuid(),
                    'name' => $childName,
                    'slug' => Str::slug($childName),
                    'description' => "Modern and elegant $childName collection.",
                    'parent_id' => $parent->id,
                    'is_active' => true
                ]);
            }
        }
    }
}