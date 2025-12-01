<?php

namespace Modules\Order\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\User\Domain\Models\User; // Use Model đúng namespace
use Modules\Product\Domain\Models\Product;
use Modules\Warehouse\Domain\Models\Warehouse;
use Modules\Inventory\Domain\Models\Inventory;
use Modules\Address\Domain\Models\Address;
use Modules\Order\Domain\Models\Order;
use Modules\Promotion\Domain\Models\Promotion;
use Modules\Order\Events\OrderCreated;
use Modules\Order\Events\OrderCancelled;
use Modules\Auth\Services\AuthService; // Import AuthService

class OrderFlowTest extends TestCase
{
    use RefreshDatabase; 

    protected $user;
    protected $token;
    protected $address;
    protected $warehouse;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([OrderCreated::class, OrderCancelled::class]);

        // 1. Setup Data
        $this->user = User::factory()->create();
        $this->address = Address::factory()->create(['user_id' => $this->user->id]);
        $this->warehouse = Warehouse::factory()->create();
        $this->product = Product::factory()->create(['price' => 100000]);

        // 2. Setup Auth Token (Fix lỗi 401)
        $authService = app(AuthService::class);
        $this->token = $authService->createAccessToken($this->user);
    }

    /** Helper để gọi API có kèm Token */
    protected function postWithToken(string $url, array $data)
    {
        return $this->postJson($url, $data, [
            'Authorization' => 'Bearer ' . $this->token
        ]);
    }

    public function test_user_can_place_order_successfully_with_promotion()
    {
        // Arrange
        Inventory::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'stock_quantity' => 10,
            'status' => 'in_stock'
        ]);

        $promotion = Promotion::create([
            'name' => 'Sale 10%',
            'type' => 'percentage',
            'value' => 10,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'status' => true
        ]);
        $promotion->products()->attach($this->product->id);

        // Act (Dùng helper postWithToken)
        $response = $this->postWithToken('/api/orders', [
            'address_id' => $this->address->id,
            'items' => [['product_uuid' => $this->product->uuid, 'quantity' => 2]],
            'notes' => 'Giao nhanh'
        ]);

        // Assert
        $response->assertStatus(201);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'total_amount' => 180000, 
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $this->product->id,
            'quantity' => 2,
            'original_price' => 100000,
            'discount_amount' => 10000,
            'unit_price' => 90000,
        ]);

        $this->assertDatabaseHas('inventories', [
            'product_id' => $this->product->id,
            'stock_quantity' => 8,
        ]);

        Event::assertDispatched(OrderCreated::class);
    }

    public function test_cannot_place_order_if_stock_is_insufficient()
    {
        Inventory::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'stock_quantity' => 1,
            'status' => 'low_stock'
        ]);

        $response = $this->postWithToken('/api/orders', [
            'address_id' => $this->address->id,
            'items' => [['product_uuid' => $this->product->uuid, 'quantity' => 2]]
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['items']);
        
        $this->assertDatabaseHas('inventories', [
            'product_id' => $this->product->id,
            'stock_quantity' => 1
        ]);
        
        Event::assertNotDispatched(OrderCreated::class);
    }

    public function test_cancelling_order_restores_inventory()
    {
        Inventory::create([
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'stock_quantity' => 10,
        ]);

        // Tạo đơn hàng trước (mua 3 cái)
        $this->postWithToken('/api/orders', [
            'address_id' => $this->address->id,
            'items' => [['product_uuid' => $this->product->uuid, 'quantity' => 3]]
        ]);

        // Verify kho còn 7
        $this->assertDatabaseHas('inventories', ['stock_quantity' => 7]);

        $order = Order::where('user_id', $this->user->id)->first();

        // Act: Hủy đơn
        $response = $this->postWithToken("/api/orders/{$order->uuid}/cancel", []);

        // Assert
        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'uuid' => $order->uuid,
            'status' => 'cancelled'
        ]);

        $this->assertDatabaseHas('inventories', [
            'product_id' => $this->product->id,
            'stock_quantity' => 10 // Đã hoàn lại đủ 3 cái
        ]);

        Event::assertDispatched(OrderCancelled::class);
    }
}