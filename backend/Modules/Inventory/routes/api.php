<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\InventoryController;

Route::middleware(['auth:sanctum'])->prefix('admin/inventories')->group(function () {
    
    Route::get('/', [InventoryController::class, 'index']);

    Route::post('/adjust', [InventoryController::class, 'adjust']);

    Route::post('/upsert', [InventoryController::class, 'upsert']);
});