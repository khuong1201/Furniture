<?php

namespace Modules\Order\tests\Feature;

use Tests\TestCase;
use Modules\Order\Domain\Models\Order;
use Modules\User\Domain\Models\User;
use Modules\Product\Domain\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_order()
    {
        // Tạo user và product hợp lệ
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 150, // nếu cần khớp với payload
        ]);

        // Payload hợp lệ
        $payload = [
            'items' => [
                [
                    'product_id' => $product->id, // hoặc $product->uuid nếu dùng UUID
                    'quantity' => 2,
                    'unit_price' => 150,
                ],
            ],
        ];

        // Gửi request với user đã đăng nhập
        $response = $this->actingAs($user, 'api')->postJson('/api/orders', $payload);

        // Kiểm tra kết quả
        $response->assertStatus(201);
        $this->assertDatabaseHas('orders', ['user_id' => $user->id]);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 150,
        ]);
    }
}
