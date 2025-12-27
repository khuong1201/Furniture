<?php

use Illuminate\Support\Facades\Route;
use Modules\Warehouse\Http\Controllers\WarehouseController;

Route::middleware(['auth:sanctum'])->prefix('admin/warehouses')->group(function () {
    Route::get('/', [WarehouseController::class, 'index']);
    Route::post('/', [WarehouseController::class, 'store']);
    Route::get('/{uuid}/stats', [WarehouseController::class, 'stats']);
    Route::get('/{uuid}', [WarehouseController::class, 'show']);
    Route::put('/{uuid}', [WarehouseController::class, 'update']);
    Route::delete('/{uuid}', [WarehouseController::class, 'destroy']);
});