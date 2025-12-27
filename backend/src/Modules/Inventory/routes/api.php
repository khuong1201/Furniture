<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\InventoryController;

Route::middleware(['auth:sanctum'])->prefix('admin/inventories')->group(function () {
    
    Route::get('/', [InventoryController::class, 'index']);
    
    Route::get('/dashboard-stats', [InventoryController::class, 'dashboardStats']);

    Route::get('/movements-chart', [InventoryController::class, 'movementsChart']);
    
    Route::post('/adjust', [InventoryController::class, 'adjust']);

    Route::post('/upsert', [InventoryController::class, 'upsert']);

    Route::get('/{uuid}', [InventoryController::class, 'show']);
});