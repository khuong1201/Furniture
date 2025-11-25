<?php

namespace Modules\Product\tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Product\Domain\Models\Product;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_blocked_by_jwt_middleware()
    {
        $response = $this->getJson('/api/products');
        $response->assertStatus(401);
    }

    public function test_create_product_success()
    {
        $this->withoutMiddleware(\Modules\Auth\Http\Middleware\JwtAuthenticate::class);

        $payload = [
            'name' => 'Test Product',
            'price' => 99.99,
            'sku' => 'SKU-TEST-001',
        ];

        $response = $this->postJson('/api/products', $payload);
        $response->assertStatus(201);
        $this->assertDatabaseHas('products', ['sku' => 'SKU-TEST-001']);
    }
}