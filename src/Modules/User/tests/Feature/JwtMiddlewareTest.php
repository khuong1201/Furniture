<?php

namespace Modules\User\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\User\Domain\Models\User;

class JwtMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_protected_route_denies_without_auth()
    {
        $response = $this->getJson('/users');
        $response->assertStatus(401);
    }

    public function test_protected_route_allows_with_valid_auth()
    {
        $user = User::factory()->create();
        // set simulated auth user for middleware as in production JwtAuthenticate sets Auth::setUser
        auth()->setUser($user);

        $response = $this->getJson('/users');
        $response->assertOk();
    }
}
