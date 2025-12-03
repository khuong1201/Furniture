<?php

use Illuminate\Support\Facades\Route;
use Modules\Shipping\Http\Controllers\ShippingController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])->prefix('admin/shippings')->group(function () {

    Route::get('/', [ShippingController::class, 'index'])
        ->middleware('permission:shipping.view');

    Route::post('/', [ShippingController::class, 'store'])
        ->middleware('permission:shipping.create');

    Route::get('/{uuid}', [ShippingController::class, 'show'])
        ->middleware('permission:shipping.view');

    Route::put('/{uuid}', [ShippingController::class, 'update'])
        ->middleware('permission:shipping.edit');

    Route::delete('/{uuid}', [ShippingController::class, 'destroy'])
        ->middleware('permission:shipping.edit'); 
});