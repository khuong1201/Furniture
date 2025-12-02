<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\Http\Controllers\OrderController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])->group(function () {
    
    Route::prefix('orders')->group(function() {

        Route::post('/', [OrderController::class, 'store']); 

        Route::post('/checkout', [OrderController::class, 'checkout']);

        Route::get('/', [OrderController::class, 'index']); 
        
        Route::get('/{uuid}', [OrderController::class, 'show']);
        
        Route::post('/{uuid}/cancel', [OrderController::class, 'cancel']);
    });
});