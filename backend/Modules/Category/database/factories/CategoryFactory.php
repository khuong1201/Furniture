<?php

namespace Modules\Category\database\factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Category\Domain\Models\Category;
use Illuminate\Support\Str;
class CategoryFactory extends Factory {
    protected $model = Category::class;
    public function definition(): array {
        $name = ucfirst($this->faker->words(3, true));
        return [
            'uuid' => $this->faker->uuid(),
            'name' =>  $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'parent_id' => null,
        ];
    }
}