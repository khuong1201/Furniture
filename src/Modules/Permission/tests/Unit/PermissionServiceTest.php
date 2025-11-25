<?php

namespace Modules\Permission\tests\Unit;

use Mockery;
use Tests\TestCase;
use Illuminate\Support\Collection;
use Modules\Permission\Services\PermissionService;
use Modules\Permission\Domain\Repositories\PermissionRepositoryInterface;
use Modules\Permission\Domain\Models\Permission;

class PermissionServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_getPermissionsByUserId_normalizes_and_unique()
    {
        $repo = Mockery::mock(PermissionRepositoryInterface::class);
        $repo->shouldReceive('getPermissionsByUserId')
            ->once()
            ->with(42)
            ->andReturn(['MANAGE_USERS', 'manage_users', 'View_Dashboard', 'view_dashboard']);

        $service = new PermissionService($repo);

        $result = $service->getPermissionsByUserId(42);

        $this->assertIsArray($result);
        // đảm bảo chỉ còn 2 giá trị duy nhất sau normalize + unique
        $this->assertCount(2, $result);
        $this->assertContains('manage_users', $result);
        $this->assertContains('view_dashboard', $result);
    }

    public function test_findByName_delegates_to_repository()
    {
        $expected = new Permission(['id' => 1, 'name' => 'foo', 'description' => 'bar']);

        $repo = Mockery::mock(PermissionRepositoryInterface::class);
        $repo->shouldReceive('findByName')
            ->once()
            ->with('foo')
            ->andReturn($expected);

        $service = new PermissionService($repo);

        $actual = $service->findByName('foo');

        $this->assertSame($expected, $actual);
    }

    public function test_create_delegates_and_returns_permission()
    {
        $payload = ['name' => 'new_perm', 'description' => 'desc'];
        $created = new Permission($payload);
        $created->id = 10;

        $repo = Mockery::mock(PermissionRepositoryInterface::class);
        $repo->shouldReceive('create')
            ->once()
            ->with($payload)
            ->andReturn($created);

        $service = new PermissionService($repo);

        $result = $service->create($payload);

        $this->assertInstanceOf(Permission::class, $result);
        $this->assertEquals(10, $result->id);
        $this->assertEquals('new_perm', $result->name);
    }

    public function test_all_delegates_and_returns_collection()
    {
        $collection = new Collection([
            new Permission(['id' => 1, 'name' => 'a']),
            new Permission(['id' => 2, 'name' => 'b']),
        ]);

        $repo = Mockery::mock(PermissionRepositoryInterface::class);
        $repo->shouldReceive('all')
            ->once()
            ->andReturn($collection);

        $service = new PermissionService($repo);

        $result = $service->all();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
        $this->assertEquals('a', $result->first()->name);
    }
}
