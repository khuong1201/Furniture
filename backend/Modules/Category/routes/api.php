<?php

use Illuminate\Support\Facades\Route;
use Modules\Category\Http\Controllers\CategoryController;

Route::prefix('public/categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{uuid}', [CategoryController::class, 'show']);
});

Route::middleware(['auth:sanctum'])->prefix('admin/categories')->group(function () {
    Route::post('/', [CategoryController::class, 'store']);
    Route::put('/{uuid}', [CategoryController::class, 'update']);
    Route::delete('/{uuid}', [CategoryController::class, 'destroy']);
});