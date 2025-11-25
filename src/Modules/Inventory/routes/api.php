<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\InventoryController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])
    ->prefix('inventories')
    ->group(function () {
        Route::get('/', [InventoryController::class, 'index']);
        Route::post('/upsert', [InventoryController::class, 'upsert']);
        Route::patch('/{productId}/{warehouseId}/stock', [InventoryController::class, 'adjust']);
        Route::get('/{uuid}', [InventoryController::class, 'show']);
        Route::delete('/{uuid}', [InventoryController::class, 'destroy']);
    });

