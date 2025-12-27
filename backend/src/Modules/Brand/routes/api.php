<?php

use Illuminate\Support\Facades\Route;
use Modules\Brand\Http\Controllers\BrandController;

Route::prefix('public')->group(function () {
    Route::get('brands', [BrandController::class, 'index']);
});

Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::apiResource('brands', BrandController::class);

    Route::get('brands-all', [BrandController::class, 'adminIndex']); 
});