<?php

namespace Modules\Category\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Category\Domain\Models\Category;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'parent_id' => null,
        ];
    }
}
