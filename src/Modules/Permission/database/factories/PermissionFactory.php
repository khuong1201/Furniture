<?php

namespace Modules\Permission\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Permission\Domain\Models\Permission;

class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
