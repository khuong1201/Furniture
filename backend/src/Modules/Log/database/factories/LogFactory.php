<?php
namespace Modules\Log\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Log\Domain\Models\Log;

class LogFactory extends Factory
{
    protected $model = Log::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'user_id' => null,
            'type' => $this->faker->randomElement(['system', 'model']),
            'action' => $this->faker->randomElement(['create', 'update', 'delete', 'error']),
            'model' => $this->faker->word(),
            'model_uuid' => $this->faker->uuid(),
            'ip_address' => $this->faker->ipv4(),
            'message' => $this->faker->sentence(),
            'metadata' => ['example' => 'data'],
        ];
    }
}
