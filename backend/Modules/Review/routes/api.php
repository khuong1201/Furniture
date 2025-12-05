<?php

use Illuminate\Support\Facades\Route;
use Modules\Review\Http\Controllers\ReviewController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::prefix('public/reviews')->group(function () {
    Route::get('/', [ReviewController::class, 'index']); 
    Route::get('/stats', [ReviewController::class, 'stats']); 
    Route::get('/{uuid}', [ReviewController::class, 'show']); 
});

Route::middleware(['auth:sanctum'])->prefix('reviews')->group(function () {
    Route::post('/', [ReviewController::class, 'store']);
    Route::put('/{uuid}', [ReviewController::class, 'update']);
    Route::delete('/{uuid}', [ReviewController::class, 'destroy']);
});