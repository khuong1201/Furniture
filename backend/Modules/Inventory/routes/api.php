<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\InventoryController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])->prefix('admin')->group(function () {
    
    Route::get('inventories', [InventoryController::class, 'index'])
        ->middleware('permission:inventory.view');

    Route::get('inventories/{uuid}', [InventoryController::class, 'show'])
        ->middleware('permission:inventory.view');
        
    Route::post('inventories/upsert', [InventoryController::class, 'upsert'])
        ->middleware('permission:inventory.edit');

    Route::patch('inventories/{productId}/{warehouseId}/adjust', [InventoryController::class, 'adjust'])
        ->middleware('permission:inventory.adjust');
        
    Route::delete('inventories/{uuid}', [InventoryController::class, 'destroy'])
        ->middleware('permission:inventory.delete');
});