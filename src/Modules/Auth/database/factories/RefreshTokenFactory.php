<?php

namespace Modules\Auth\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Auth\Models\RefreshToken;
use Modules\User\Models\User;
use Illuminate\Support\Str;

class RefreshTokenFactory extends Factory
{
    protected $model = RefreshToken::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'token' => Str::random(128),
            'device_name' => 'testing',
            'ip' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'expires_at' => now()->addDays(7),
        ];
    }
}
