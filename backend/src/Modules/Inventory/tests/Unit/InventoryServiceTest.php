<?php

namespace Modules\Inventory\Tests\Unit;

use Tests\TestCase;
use Modules\Inventory\Services\InventoryService;
use Modules\Inventory\Domain\Repositories\InventoryRepositoryInterface;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use Modules\Warehouse\Domain\Repositories\WarehouseRepositoryInterface;
use Modules\Inventory\Domain\Models\Inventory;
use Mockery;

class InventoryServiceTest extends TestCase
{
    protected $inventoryRepo;
    protected $productRepo;
    protected $warehouseRepo;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->inventoryRepo = Mockery::mock(InventoryRepositoryInterface::class);
        $this->productRepo = Mockery::mock(ProductRepositoryInterface::class);
        $this->warehouseRepo = Mockery::mock(WarehouseRepositoryInterface::class);

        $this->service = new InventoryService(
            $this->inventoryRepo,
            $this->productRepo,
            $this->warehouseRepo
        );
    }

    public function test_adjust_stock_increases_quantity_correctly()
    {
        $inventory = new Inventory([
            'stock_quantity' => 10, 
            'min_threshold' => 5
        ]);

        $this->inventoryRepo
            ->shouldReceive('findByProductAndWarehouse')
            ->with(1, 1, true) 
            ->once()
            ->andReturn($inventory);

        $this->inventoryRepo
            ->shouldReceive('update')
            ->with($inventory, [
                'stock_quantity' => 15,
                'status' => 'in_stock'
            ])
            ->once()
            ->andReturn($inventory);

        $this->service->adjustStock(1, 1, 5);
        
        $this->assertTrue(true); 
    }

    public function test_adjust_stock_throws_exception_if_insufficient()
    {
        $inventory = new Inventory(['stock_quantity' => 2]);

        $this->inventoryRepo
            ->shouldReceive('findByProductAndWarehouse')
            ->andReturn($inventory);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->service->adjustStock(1, 1, -5);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}