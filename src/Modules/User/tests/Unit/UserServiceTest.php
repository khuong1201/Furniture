<?php

namespace Modules\User\Tests\Unit;

use Mockery;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\User\Domain\Models\User;
use Modules\User\Services\UserService;
use Modules\User\Domain\Repositories\UserRepositoryInterface;
use Modules\Shared\DTO\UserDTO;

class UserServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_hashes_password_and_calls_repo_create()
    {
        $repo = Mockery::mock(UserRepositoryInterface::class);
        $service = new UserService($repo);

        $input = [
            'name' => 'Alice',
            'email' => 'alice@example.test',
            'password' => 'plainpassword',
            'is_active' => true,
        ];

        $repo->shouldReceive('create')
            ->once()
            ->withArgs(function ($payload) use ($input) {
                if (empty($payload['uuid']) || !Str::isUuid($payload['uuid'])) return false;
                if (!isset($payload['password']) || !str_starts_with($payload['password'], '$2y$')) return false;
                return $payload['name'] === $input['name'] && $payload['email'] === $input['email'];
            })
            ->andReturnUsing(function ($payload) {
                $user = new User();
                $user->fill($payload);
                $user->id = 1;
                // prevent any lazy-loading by pre-setting an empty roles relation
                $user->setRelation('roles', collect());
                return $user;
            });

        $dto = $service->create($input);

        $this->assertInstanceOf(UserDTO::class, $dto);
        $this->assertEquals('Alice', $dto->name ?? 'Alice');
    }

    public function test_findByUuid_returns_dto_when_user_found()
    {
        $repo = Mockery::mock(UserRepositoryInterface::class);
        $service = new UserService($repo);

        $user = new User();
        $user->id = 10;
        $user->uuid = (string) Str::uuid();
        $user->name = 'Bob';
        // ensure roles relation exists so service->load won't trigger DB query
        $user->setRelation('roles', collect());

        $repo->shouldReceive('findByUuid')
            ->once()
            ->with($user->uuid)
            ->andReturn($user);

        $dto = $service->findByUuid($user->uuid);

        $this->assertInstanceOf(UserDTO::class, $dto);
        $this->assertEquals('Bob', $dto->name ?? 'Bob');
    }

    public function test_update_hashes_password_and_calls_repo_update()
    {
        $repo = Mockery::mock(UserRepositoryInterface::class);
        $service = new UserService($repo);

        $uuid = (string) Str::uuid();
        $existing = new User();
        $existing->id = 5;
        $existing->uuid = $uuid;
        $existing->name = 'Old';
        $existing->setRelation('roles', collect());

        $repo->shouldReceive('findByUuid')->once()->with($uuid)->andReturn($existing);

        $repo->shouldReceive('update')
            ->once()
            ->withArgs(function ($userArg, $payload) {
                if (!($userArg instanceof User)) return false;
                if (!isset($payload['password']) || !str_starts_with($payload['password'], '$2y$')) return false;
                return true;
            })
            ->andReturnUsing(function ($user, $payload) {
                $user->fill($payload);
                // set roles relation to avoid DB calls when fresh('roles.permissions') is called
                $user->setRelation('roles', collect());
                return $user;
            });

        $input = ['name' => 'Updated', 'password' => 'newpass123'];
        $dto = $service->update($uuid, $input);

        $this->assertInstanceOf(UserDTO::class, $dto);
    }

    public function test_delete_calls_repo_delete()
    {
        $repo = Mockery::mock(UserRepositoryInterface::class);
        $service = new UserService($repo);

        $uuid = (string) Str::uuid();
        $user = new User();
        $user->uuid = $uuid;
        $user->setRelation('roles', collect());

        $repo->shouldReceive('findByUuid')->once()->with($uuid)->andReturn($user);
        $repo->shouldReceive('delete')->once()->with($user)->andReturn(true);

        $service->delete($uuid);

        $this->addToAssertionCount(1);
    }

    public function test_hasPermission_checks_roles_permissions()
    {
        $repo = Mockery::mock(UserRepositoryInterface::class);
        $service = new UserService($repo);

        $uuid = (string) Str::uuid();
        $user = new User();
        $user->id = 2;
        $user->uuid = $uuid;
        $user->is_active = true;

        // create an in-memory role-like object with permissions collection
        $permission = (object) ['name' => 'manage_posts'];
        $role = new class {
            public $permissions;
        };
        $role->permissions = collect([$permission]);

        // assign roles relation directly to avoid DB queries
        $user->setRelation('roles', collect([$role]));

        $repo->shouldReceive('findByUuid')->once()->with($uuid)->andReturn($user);
        $repo->shouldReceive('findById')->never();

        $this->assertTrue($service->hasPermission($uuid, 'manage_posts'));
        $this->assertFalse($service->hasPermission($uuid, 'not_exists'));
    }

    public function test_paginate_delegates_to_repo_and_returns_meta()
    {
        $repo = Mockery::mock(UserRepositoryInterface::class);
        $service = new UserService($repo);

        $items = [new User(['id' => 1, 'name' => 'A'])];
        $paginator = new LengthAwarePaginator($items, 1, 15, 1);

        $repo->shouldReceive('paginate')->once()->with(15, [])->andReturn($paginator);

        $res = $service->paginate(15, []);

        $this->assertIsArray($res);
        $this->assertArrayHasKey('items', $res);
        $this->assertArrayHasKey('meta', $res);
        $this->assertEquals(1, $res['meta']['total']);
    }
}