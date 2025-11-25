<?php

namespace Modules\Shipping\tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\User\Domain\Models\User;
use Modules\Order\Domain\Models\Order;
use Modules\Shipping\Domain\Models\Shipping;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

class ShippingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Bỏ middleware xác thực nếu đang dùng JWT
        $this->withoutMiddleware(JwtAuthenticate::class);
    }

    public function test_user_can_create_shipping()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'api');

        $response = $this->postJson('/api/shippings', [
            'order_id' => $order->id,
            'provider' => 'DHL',
            'tracking_number' => 'TRK123456',
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['provider' => 'DHL']);
    }

    public function test_user_can_update_shipping()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $shipping = Shipping::factory()->create([
            'order_id' => $order->id,
            'provider' => 'VNPost',
            'tracking_number' => 'TRK999999'
        ]);

        $this->actingAs($user, 'api');

        $response = $this->putJson("/api/shippings/{$shipping->uuid}", [
            'provider' => 'GHN',
            'status' => 'shipped'
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['provider' => 'GHN', 'status' => 'shipped']);
    }

    public function test_user_can_delete_shipping()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $shipping = Shipping::factory()->create([
            'order_id' => $order->id
        ]);

        $this->actingAs($user, 'api');

        $response = $this->deleteJson("/api/shippings/{$shipping->uuid}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Shipping deleted successfully']);
    }
}