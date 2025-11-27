<?php

namespace Modules\Product\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Product\Domain\Models\ProductImage;

class ProductImageFactory extends Factory
{
    protected $model = ProductImage::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'image_url' => $this->faker->imageUrl(800, 600),
            'public_id' => null,
            'is_primary' => false,
        ];
    }
}
