<?php

namespace Modules\Warehouse\tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Warehouse\Domain\Models\Warehouse;

class WarehouseTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_blocked_by_jwt()
    {
        $response = $this->getJson('/api/warehouses');
        $response->assertStatus(401);
    }

    public function test_create_warehouse()
    {
        $this->withoutMiddleware(\Modules\Auth\Http\Middleware\JwtAuthenticate::class);
        $payload = ['name'=>'Kho HN','location'=>'HN'];
        $response = $this->postJson('/api/warehouses', $payload);
        $response->assertStatus(201);
        $this->assertDatabaseHas('warehouses', ['name'=>'Kho HN']);
    }
}
