<?php

namespace Modules\Address\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Address\Domain\Models\Address;
use Modules\User\Domain\Models\User;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'full_name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'province' => $this->faker->city(),
            'district' => $this->faker->streetName(),
            'ward' => 'Ward ' . $this->faker->numberBetween(1, 10),
            'street' => $this->faker->streetAddress(),
            'is_default' => false,
        ];
    }
}