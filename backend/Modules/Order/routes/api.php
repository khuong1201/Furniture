<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\Http\Controllers\OrderController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::prefix('orders')->middleware([JwtAuthenticate::class])->group(function () {
    Route::get('/', [OrderController::class, 'order.index']);
    Route::get('/{uuid}', [OrderController::class, 'order.show']); 
    Route::post('/', [OrderController::class, 'order.store']);
    Route::put('/{uuid}', [OrderController::class, 'order.update']);
    Route::delete('/{uuid}', [OrderController::class, 'order.destroy']);
});

