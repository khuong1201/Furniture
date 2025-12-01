<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\Http\Controllers\OrderController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])->group(function () {
    
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{uuid}', [OrderController::class, 'show']);
    Route::post('orders/{uuid}/cancel', [OrderController::class, 'cancel']);
});