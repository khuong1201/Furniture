<?php

use Illuminate\Support\Facades\Route;
use Modules\Review\Http\Controllers\ReviewController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

// Public Read
Route::get('reviews', [ReviewController::class, 'index']);

// Protected Write
Route::middleware(['auth:sanctum'])->prefix('reviews')->group(function () {
    Route::post('/', [ReviewController::class, 'store']);
    Route::put('/{uuid}', [ReviewController::class, 'update']);
    Route::delete('/{uuid}', [ReviewController::class, 'destroy']);
});