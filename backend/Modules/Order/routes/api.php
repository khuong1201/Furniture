<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\Http\Controllers\OrderController;

Route::middleware(['auth:sanctum'])->prefix('orders')->group(function() {
    
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::get('/', [OrderController::class, 'index']); 
    Route::get('/{uuid}', [OrderController::class, 'show']);
    Route::post('/{uuid}/cancel', [OrderController::class, 'cancel']);

    Route::get('/stats/all', [OrderController::class, 'stats']);
    Route::post('/admin/create', [OrderController::class, 'store']); 
    Route::put('/{uuid}/status', [OrderController::class, 'updateStatus']);
});