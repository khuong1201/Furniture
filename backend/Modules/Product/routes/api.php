<?php
use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\ProductController;
use Modules\Product\Http\Controllers\ProductImageController;
use Modules\Product\Http\Controllers\AttributeController;

Route::prefix('public/products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{uuid}', [ProductController::class, 'show']);
});

Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::get('products', [ProductController::class, 'adminIndex']);
    Route::post('products', [ProductController::class, 'store']);
    Route::put('products/{uuid}', [ProductController::class, 'update']);
    Route::delete('products/{uuid}', [ProductController::class, 'destroy']);

    Route::post('products/{uuid}/images', [ProductImageController::class, 'store']);
    Route::delete('product-images/{uuid}', [ProductImageController::class, 'destroy']);

    Route::apiResource('attributes', AttributeController::class);
});