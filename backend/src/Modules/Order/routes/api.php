<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\Http\Controllers\OrderController;

Route::middleware(['auth:sanctum'])->group(function() {
    
    Route::prefix('orders')->group(function() {
        Route::post('/checkout', [OrderController::class, 'checkout']);
        Route::post('/buy-now', [OrderController::class, 'buyNow']); 
        
        Route::get('/', [OrderController::class, 'index']); 
        Route::get('/{uuid}', [OrderController::class, 'show']);
        
        Route::post('/{uuid}/cancel', [OrderController::class, 'cancel']);
    });

    Route::prefix('admin/orders')->group(function() {
        Route::get('/', [OrderController::class, 'index']); 
        Route::get('/{uuid}', [OrderController::class, 'show']);
        
        Route::put('/{uuid}/status', [OrderController::class, 'updateStatus']);
        Route::post('/create', [OrderController::class, 'store']); 
    });
});