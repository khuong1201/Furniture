<?php

namespace Modules\Inventory\tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Product\Domain\Models\Product;
use Modules\Warehouse\Domain\Models\Warehouse;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_upsert_inventory()
    {
        $this->withoutMiddleware(\Modules\Auth\Http\Middleware\JwtAuthenticate::class);

        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $payload = [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 50,
            'min_threshold' => 5,
        ];

        $response = $this->postJson('/api/inventories/upsert', $payload);
        $response->assertStatus(201);
        $this->assertDatabaseHas('inventories', ['product_id' => $product->id, 'warehouse_id' => $warehouse->id, 'stock_quantity' => 50]);
    }

    public function test_adjust_stock()
    {
        $this->withoutMiddleware(\Modules\Auth\Http\Middleware\JwtAuthenticate::class);

        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $this->postJson('/api/inventories/upsert', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 20
        ]);

        $response = $this->postJson("/api/inventories/{$product->id}/{$warehouse->id}/adjust", ['delta' => -5]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('inventories', ['product_id' => $product->id, 'warehouse_id' => $warehouse->id, 'stock_quantity' => 15]);
    }
}
