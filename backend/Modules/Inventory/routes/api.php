<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\InventoryController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])
    ->prefix('inventories')
    ->group(function () {
        Route::get('/', [InventoryController::class, 'inventory.index']);
        Route::post('/upsert', [InventoryController::class, 'inventory.upsert']);
        Route::patch('/{productId}/{warehouseId}/stock', [InventoryController::class, 'inventory.adjust']);
        Route::get('/{uuid}', [InventoryController::class, 'inventory.show']);
        Route::delete('/{uuid}', [InventoryController::class, 'inventory.destroy']);
    });

