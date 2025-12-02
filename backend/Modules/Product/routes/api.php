<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\ProductController;
use Modules\Product\Http\Controllers\ProductImageController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])->prefix('admin')->group(function () {
    
    Route::get('products', [ProductController::class, 'index'])
        ->middleware('permission:product.view')
        ->name('products.index');
        
    Route::get('products/{uuid}', [ProductController::class, 'show'])
        ->middleware('permission:product.view')
        ->name('products.show');

    Route::post('products', [ProductController::class, 'store'])
        ->middleware('permission:product.create')
        ->name('products.store');

    Route::post('products/{uuid}', [ProductController::class, 'update']) 
        ->middleware('permission:product.edit')
        ->name('products.update');

    Route::delete('products/{uuid}', [ProductController::class, 'destroy'])
        ->middleware('permission:product.delete')
        ->name('products.destroy');

    Route::delete('product-images/{uuid}', [ProductImageController::class, 'destroy'])
        ->middleware('permission:product.edit')
        ->name('product-images.destroy');
});
Route::prefix('public')->group(function () {
    Route::get('products', [ProductController::class, 'index'])
        ->name('products.user.index');

    Route::get('products/{uuid}', [ProductController::class, 'show'])
        ->name('products.user.show');
});