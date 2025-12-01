<?php

use Illuminate\Support\Facades\Route;
use Modules\Shipping\Http\Controllers\ShippingController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])->prefix('admin')->group(function () {
    
    Route::apiResource('shippings', ShippingController::class)
        ->parameters(['shippings' => 'uuid'])
        ->middleware([
            // 'store' => 'permission:shipping.create',
            // 'update' => 'permission:shipping.edit',
        ]);
});