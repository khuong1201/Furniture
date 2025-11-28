<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\ProductController;
use Modules\Product\Http\Controllers\ProductImageController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])->group(function () {
    Route::get('products', [ProductController::class,'product.index']);
    Route::post('products', [ProductController::class,'product.store']);
    Route::get('products/{uuid}', [ProductController::class,'product.show']);
    Route::put('products/{uuid}', [ProductController::class,'product.update']);
    Route::delete('products/{uuid}', [ProductController::class,'product.destroy']);

    Route::post('products/{uuid}/images', [ProductImageController::class,'product_image.store']);
    Route::delete('product-images/{uuid}', [ProductImageController::class,'product_image.destroy']);
});

