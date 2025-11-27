<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\Http\Controllers\OrderController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::prefix('orders')->middleware([JwtAuthenticate::class])->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::get('/{uuid}', [OrderController::class, 'show']); 
    Route::post('/', [OrderController::class, 'store']);
    Route::put('/{uuid}', [OrderController::class, 'update']);
    Route::delete('/{uuid}', [OrderController::class, 'destroy']);
});

