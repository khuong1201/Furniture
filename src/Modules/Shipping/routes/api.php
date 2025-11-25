<?php

use Illuminate\Support\Facades\Route;
use Modules\Shipping\Http\Controllers\ShippingController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::prefix('shippings')->middleware([JwtAuthenticate::class])->group(function () {
    Route::get('/', [ShippingController::class, 'index']);
    Route::post('/', [ShippingController::class, 'store']);
    Route::put('/{uuid}', [ShippingController::class, 'update']);
    Route::delete('/{uuid}', [ShippingController::class, 'destroy']);
});
