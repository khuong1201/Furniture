<?php

namespace Modules\Address\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Address\Domain\Models\Address;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'full_name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'province' => $this->faker->city(),
            'district' => $this->faker->streetName(),
            'ward' => $this->faker->streetSuffix(),
            'street' => $this->faker->address(),
            'is_default' => $this->faker->boolean(30),
        ];
    }
}
