<?php

namespace Modules\Role\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Role\Domain\Models\Role;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
