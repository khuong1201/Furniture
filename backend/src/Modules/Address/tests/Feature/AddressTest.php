<?php

namespace Modules\Address\tests\Feature;

use Tests\TestCase;
use Modules\Address\Domain\Models\Address;
use Modules\User\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
class AddressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Modules\Auth\Http\Middleware\JwtAuthenticate::class);
    }

    public function test_user_can_create_address()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson('/api/address', [
            'full_name' => 'John Doe',
            'phone' => '0123456789',
            'province' => 'HCM',
            'district' => 'Go Vap',
            'ward' => 'Ward 8',
            'street' => '123 Main St',
        ]);

        $response->assertStatus(201);
    }
}
