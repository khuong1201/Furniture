<?php

namespace Modules\Promotion\tests\Feature;

use Tests\TestCase;
use Modules\User\Domain\Models\User;
use Modules\Promotion\Domain\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Http\Middleware\JwtAuthenticate;
class PromotionTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_promotion()
    {
        $this->withoutMiddleware(JwtAuthenticate::class);
        $user = User::factory()->create();
        $this->actingAs($user, 'api');
        $response = $this->postJson('/api/promotions', [
            'name' => 'Black Friday Sale',
            'type' => 'percentage',
            'value' => 20,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
        ]);

        // Kiểm tra phản hồi
        $response->assertStatus(201);
    }
}
