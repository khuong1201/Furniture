<?php

namespace Modules\Category\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Category\Domain\Models\Category;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        // [FIX LỖI] Thay 'department' bằng 'word' và viết hoa chữ cái đầu
        $name = ucfirst($this->faker->word); 

        return [
            'uuid'      => (string) Str::uuid(),
            'name'      => $name,
            'slug'      => Str::slug($name),
            'is_active' => true,
            'parent_id' => null,
            'image'     => "https://placehold.co/300x300?text=" . urlencode($name),
        ];
    }
}