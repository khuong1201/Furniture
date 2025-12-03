<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\InventoryController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])->prefix('admin/inventories')->group(function () {
    
    Route::get('/', [InventoryController::class, 'index'])
        ->middleware('permission:inventory.view');

    Route::post('/adjust', [InventoryController::class, 'adjust'])
        ->middleware('permission:inventory.adjust');

    Route::post('/upsert', [InventoryController::class, 'upsert'])
        ->middleware('permission:inventory.edit');
});