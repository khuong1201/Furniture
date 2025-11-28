<?php

use Illuminate\Support\Facades\Route;
use Modules\Warehouse\Http\Controllers\WarehouseController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])
    ->prefix('warehouses')
    ->group(function () {
        Route::get('/', [WarehouseController::class, 'warehouse.index']);
        Route::post('/', [WarehouseController::class, 'warehouse.store']);
        Route::get('/{uuid}', [WarehouseController::class, 'warehouse.show']);
        Route::put('/{uuid}', [WarehouseController::class, 'warehouse.update']);
        Route::delete('/{uuid}', [WarehouseController::class, 'warehouse.destroy']);
    });
