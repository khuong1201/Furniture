<?php

use Illuminate\Support\Facades\Route;
use Modules\Shipping\Http\Controllers\ShippingController;

Route::middleware(['auth:sanctum']) 
    ->prefix('admin/shippings')
    ->group(function () {
        Route::get('/', [ShippingController::class, 'index']);
        Route::post('/', [ShippingController::class, 'store']);
        Route::get('/{uuid}', [ShippingController::class, 'show']);
        Route::put('/{uuid}', [ShippingController::class, 'update']);
        Route::delete('/{uuid}', [ShippingController::class, 'destroy']); 
    });