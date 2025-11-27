<?php

namespace Modules\Payment\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Order\Domain\Models\Order;
use Modules\User\Domain\Models\User;
use Modules\Payment\Domain\Models\Payment;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_payment()
    {
        // Bỏ middleware JWT nếu đang dùng
        $this->withoutMiddleware(JwtAuthenticate::class);

        // Arrange: Tạo user và order mẫu
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $order = Order::factory()->create();

        // Giả lập đăng nhập
        $this->actingAs($user, 'api'); // hoặc 'web' tùy guard bạn dùng

        // Act: Gửi request tạo payment
        $response = $this->postJson('/api/payments', [
            'order_id' => $order->id,
            'method' => 'paypal',
            'status' => 'paid',
        ]);

        // Assert: Kiểm tra phản hồi và DB
        $response->assertStatus(201);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => 'paypal',
            'status' => 'paid',
        ]);
    }
}
