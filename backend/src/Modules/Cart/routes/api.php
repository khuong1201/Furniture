<?php

use Illuminate\Support\Facades\Route;
use Modules\Cart\Http\Controllers\CartController;
use Modules\Cart\Http\Controllers\CartVoucherController;


Route::middleware(['auth:sanctum'])->prefix('carts')->group(function() {

    Route::get('/', [CartController::class, 'index']);      
    Route::post('/', [CartController::class, 'store']);       
    Route::delete('/', [CartController::class, 'clear']);     
    
    Route::put('/{itemUuid}', [CartController::class, 'update']); 
    Route::delete('/{itemUuid}', [CartController::class, 'destroy']); 
    
    Route::post('/bulk-delete', [CartController::class, 'bulkDestroy']); 

    Route::post('/apply-coupon', [CartVoucherController::class, 'apply']);
    Route::delete('/remove-coupon', [CartVoucherController::class, 'remove']);
});