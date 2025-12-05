<?php

use Illuminate\Support\Facades\Route;
use Modules\Cart\Http\Controllers\CartController;
use Modules\Cart\Http\Controllers\CartVoucherController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['auth:sanctum'])->prefix('carts')->group(function() {
    Route::get('/', [CartController::class, 'index']);   
    Route::post('/', [CartController::class, 'store']);   
    Route::put('/{itemUuid}', [CartController::class, 'update']);
    Route::delete('/{uuid}', [CartController::class, 'destroy']); 
    Route::delete('/', [CartController::class, 'clear']); 
    Route::post('/apply-coupon', [CartVoucherController::class, 'apply']);
    Route::delete('/remove-coupon', [CartVoucherController::class, 'remove']);
});