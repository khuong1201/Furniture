<?php

use Illuminate\Support\Facades\Route;
use Modules\Collection\Http\Controllers\CollectionController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

// Public Routes
Route::prefix('public/collections')->group(function () {
    Route::get('/', [CollectionController::class, 'index']);
    Route::get('/{uuid}', [CollectionController::class, 'show']);
});

// Admin Routes
Route::middleware(['auth:sanctum'])->prefix('admin/collections')->group(function () {
    Route::post('/', [CollectionController::class, 'store']);
    
    // Dùng POST để update file (Laravel limitation with PUT/PATCH form-data)
    // Hoặc dùng PUT bình thường nếu client gửi JSON (không update ảnh)
    Route::match(['put', 'post'], '/{uuid}', [CollectionController::class, 'update']);
    
    Route::delete('/{uuid}', [CollectionController::class, 'destroy']);
});