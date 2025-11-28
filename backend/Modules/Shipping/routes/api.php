<?php

use Illuminate\Support\Facades\Route;
use Modules\Shipping\Http\Controllers\ShippingController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::prefix('shippings')->middleware([JwtAuthenticate::class])->group(function () {
    Route::get('/', [ShippingController::class, 'shipping.index']);
    Route::post('/', [ShippingController::class, 'shipping.store']);
    Route::put('/{uuid}', [ShippingController::class, 'shipping.update']);
    Route::delete('/{uuid}', [ShippingController::class, 'shipping.destroy']);
});
