<?php

use Illuminate\Support\Facades\Route;
use Modules\Warehouse\Http\Controllers\WarehouseController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])->prefix('admin')->group(function () {
    
    Route::apiResource('warehouses', WarehouseController::class)
        ->parameters(['warehouses' => 'uuid']);
});