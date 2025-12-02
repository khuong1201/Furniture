<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\ProductController;
use Modules\Product\Http\Controllers\ProductImageController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::prefix('public/products')->group(function () {
    Route::get('/', [ProductController::class, 'index'])
        ->name('products.user.index');

    Route::get('/{uuid}', [ProductController::class, 'show'])
        ->name('products.user.show');
});

Route::middleware(['api', JwtAuthenticate::class])->prefix('admin')->group(function () {
    
    Route::get('products', [ProductController::class, 'adminIndex'])
        ->middleware('permission:product.view');

    Route::post('products', [ProductController::class, 'store'])
        ->middleware('permission:product.create');

    Route::put('products/{uuid}', [ProductController::class, 'update']) 
        ->middleware('permission:product.edit');

    Route::delete('products/{uuid}', [ProductController::class, 'destroy'])
        ->middleware('permission:product.delete');

    Route::post('products/{uuid}/images', [ProductImageController::class, 'store'])
        ->middleware('permission:product.edit');

    Route::delete('product-images/{uuid}', [ProductImageController::class, 'destroy'])
        ->middleware('permission:product.edit');
});