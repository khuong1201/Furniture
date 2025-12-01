<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\Http\Controllers\OrderController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])->group(function () {
    
    Route::post('orders', [OrderController::class, 'store'])
        ->middleware('permission:order.create');
        
    Route::post('orders/checkout', [OrderController::class, 'checkout'])
        ->middleware('permission:order.create');
    
    Route::get('orders', [OrderController::class, 'index'])
        ->middleware('permission:order.view');

    Route::get('orders/{uuid}', [OrderController::class, 'show'])
        ->middleware('permission:order.view');

    Route::post('orders/{uuid}/cancel', [OrderController::class, 'cancel'])
        ->middleware('permission:order.cancel');
});