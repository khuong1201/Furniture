<?php

namespace Modules\User\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\User\Domain\Models\User;

class UserFactory extends Factory
{
    // Chỉ định Model tương ứng
    protected $model = User::class;

    // Biến tĩnh để lưu hash password (tối ưu hiệu năng)
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'uuid'              => (string) Str::uuid(),
            'name'              => $this->faker->name(),
            'email'             => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            // Hash 1 lần dùng cho toàn bộ, thay vì hash 20 lần
            'password'          => static::$password ??= Hash::make('password'),
            'is_active'         => true,
        ];
    }
}