<?php

namespace Modules\Auth\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Domain\Repositories\AuthRepositoryInterface;
use Modules\User\Domain\Models\User;

class AuthRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected AuthRepositoryInterface $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = app(AuthRepositoryInterface::class);
    }

    public function test_find_by_email_returns_user()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $found = $this->repo->findByEmail('test@example.com');

        $this->assertNotNull($found);
        $this->assertEquals($user->id, $found->id);
    }

    public function test_find_by_id_returns_user()
    {
        $user = User::factory()->create();
        $found = $this->repo->findById($user->id);

        $this->assertNotNull($found);
        $this->assertEquals($user->email, $found->email);
    }

    public function test_create_user_saves_to_database()
    {
        $data = [
            'name' => 'Test User',
            'email' => 'new@example.com',
            'password' => bcrypt('password123'),
        ];

        $user = $this->repo->createUser($data);
        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
        $this->assertEquals('Test User', $user->name);
    }
}
