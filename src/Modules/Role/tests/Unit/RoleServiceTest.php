<?php

namespace Modules\Role\tests\Unit;

use Mockery;
use Tests\TestCase;
use Modules\Role\Services\RoleService;
use Modules\Role\Domain\Repositories\RoleRepositoryInterface;
use Modules\Role\Domain\Models\Role;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RoleServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_role_delegates_to_repository_and_returns_model()
    {
        $repo = Mockery::mock(RoleRepositoryInterface::class);
        $service = new RoleService($repo);

        $payload = ['name' => 'editor', 'description' => 'Editor role'];

        $repo->shouldReceive('create')
            ->once()
            ->with($payload)
            ->andReturnUsing(function ($data) {
                $role = new Role($data);
                $role->id = 1;
                return $role;
            });

        $role = $service->createRole($payload);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals('editor', $role->name);
    }

    public function test_update_role_calls_repo_and_returns_updated()
    {
        $repo = Mockery::mock(RoleRepositoryInterface::class);
        $service = new RoleService($repo);

        $role = new Role(['name' => 'old']);
        $role->id = 5;

        $repo->shouldReceive('findById')->once()->with(5)->andReturn($role);
        $repo->shouldReceive('update')->once()->with($role, ['name' => 'new'])->andReturnUsing(function ($r, $data) {
            $r->fill($data);
            return $r;
        });

        $updated = $service->updateRole(5, ['name' => 'new']);

        $this->assertInstanceOf(Role::class, $updated);
        $this->assertEquals('new', $updated->name);
    }

    public function test_update_role_throws_when_not_found()
    {
        $repo = Mockery::mock(RoleRepositoryInterface::class);
        $service = new RoleService($repo);

        $repo->shouldReceive('findById')->once()->with(999)->andReturn(null);

        $this->expectException(ModelNotFoundException::class);

        $service->updateRole(999, ['name' => 'irrelevant']);
    }

    public function test_delete_role_calls_repo_delete()
    {
        $repo = Mockery::mock(RoleRepositoryInterface::class);
        $service = new RoleService($repo);

        $role = new Role(['name' => 'to-delete']);
        $role->id = 3;

        $repo->shouldReceive('findById')->once()->with(3)->andReturn($role);
        $repo->shouldReceive('delete')->once()->with($role)->andReturn(true);

        $res = $service->deleteRole(3);

        $this->assertTrue($res);
    }

    public function test_list_roles_delegates_to_repo()
    {
        $repo = Mockery::mock(RoleRepositoryInterface::class);
        $service = new RoleService($repo);

        $paginator = \Mockery::mock(\Illuminate\Pagination\LengthAwarePaginator::class);
        $repo->shouldReceive('all')->once()->with(15, [])->andReturn($paginator);

        $res = $service->listRoles(15, []);

        $this->assertSame($paginator, $res);
    }
}
