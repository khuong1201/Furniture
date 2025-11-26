<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\ProductController;
use Modules\Product\Http\Controllers\ProductImageController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])->group(function () {
    Route::get('products', [ProductController::class,'index']);
    Route::post('products', [ProductController::class,'store']);
    Route::get('products/{uuid}', [ProductController::class,'show']);
    Route::put('products/{uuid}', [ProductController::class,'update']);
    Route::delete('products/{uuid}', [ProductController::class,'destroy']);

    Route::post('products/{uuid}/images', [ProductImageController::class,'store']);
    Route::delete('product-images/{uuid}', [ProductImageController::class,'destroy']);
});

