<?php

use Illuminate\Support\Facades\Route;
use Modules\Warehouse\Http\Controllers\WarehouseController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])->prefix('admin')->group(function () {
    
    Route::get('warehouses', [WarehouseController::class, 'index'])
        ->middleware('permission:warehouse.view');

    Route::post('warehouses', [WarehouseController::class, 'store'])
        ->middleware('permission:warehouse.create');

    Route::get('warehouses/{uuid}', [WarehouseController::class, 'show'])
        ->middleware('permission:warehouse.view');

    Route::put('warehouses/{uuid}', [WarehouseController::class, 'update'])
        ->middleware('permission:warehouse.edit');

    Route::delete('warehouses/{uuid}', [WarehouseController::class, 'destroy'])
        ->middleware('permission:warehouse.delete');
});
