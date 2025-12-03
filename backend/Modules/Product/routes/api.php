<?php use Illuminate\Support\Facades\Route; use Modules\Product\Http\Controllers\ProductController; use Modules\Product\Http\Controllers\AttributeController; use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::prefix('public/products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{uuid}', [ProductController::class, 'show']);
});

Route::middleware(['api', JwtAuthenticate::class])->prefix('admin')->group(function () {
    Route::get('products', [ProductController::class, 'adminIndex'])->middleware('permission:product.view');
    Route::post('products', [ProductController::class, 'store'])->middleware('permission:product.create');
    Route::put('products/{uuid}', [ProductController::class, 'update'])->middleware('permission:product.edit');
    Route::delete('products/{uuid}', [ProductController::class, 'destroy'])->middleware('permission:product.delete');
    
    Route::apiResource('attributes', AttributeController::class)->parameters(['attributes' => 'uuid']);
});