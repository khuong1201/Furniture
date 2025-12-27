<?php

namespace Modules\Brand\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Brand\Domain\Models\Brand;
use Illuminate\Support\Str;

class BrandDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            'IKEA'                 => 'Minimalist functional furniture.',
            'Ashley Furniture'     => 'Traditional American home style.',
            'Herman Miller'        => 'Ergonomic office chairs and modern design.',
            'West Elm'             => 'Modern furniture and home decor.',
            'Restoration Hardware' => 'Luxury classic and rustic furniture.',
            'Steelcase'            => 'High-performance office furniture.',
            'Crate & Barrel'       => 'Contemporary and modern furniture.',
            'La-Z-Boy'             => 'Comfortable recliners and sofas.',
        ];

        foreach ($brands as $name => $desc) {
            $slug = Str::slug($name);

            if (Brand::where('slug', $slug)->exists()) {
                continue;
            }

            Brand::factory()->create([
                'name'        => $name,
                'slug'        => $slug,
                'description' => $desc,
                'logo_url'    => "https://placehold.co/200x200?text=" . urlencode($name)
            ]);
        }
    }
}