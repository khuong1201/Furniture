<?php

namespace Modules\User\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\User\Domain\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'), 
            'is_active' => true,
            'avatar_url' => $this->faker->imageUrl(200, 200, 'people'),
        ];
    }
    
    public function admin()
    {
        return $this->state(fn (array $attributes) => [
            'email' => 'admin@system.com',
        ]);
    }
}