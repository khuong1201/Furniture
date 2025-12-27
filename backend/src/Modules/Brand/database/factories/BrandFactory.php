<?php

namespace Modules\Brand\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Brand\Domain\Models\Brand;

class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        $name = $this->faker->company;

        return [
            'uuid'        => (string) Str::uuid(),
            'name'        => $name,
            'slug'        => Str::slug($name),
            'description' => $this->faker->catchPhrase,
            'is_active'   => true,
            'sort_order'  => 0,
            // Placeholder mặc định theo tên random
            'logo_url'    => "https://placehold.co/200x200?text=" . urlencode($name),
        ];
    }
}