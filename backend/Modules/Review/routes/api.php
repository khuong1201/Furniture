<?php

use Illuminate\Support\Facades\Route;
use Modules\Review\Http\Controllers\ReviewController;

Route::prefix('public/reviews')->group(function () {
    Route::get('/', [ReviewController::class, 'index']);     
    Route::get('/stats', [ReviewController::class, 'stats']); 
    Route::get('/{uuid}', [ReviewController::class, 'show']); 
});

Route::middleware(['auth:sanctum'])->group(function () {
    
    Route::get('admin/reviews', [ReviewController::class, 'adminIndex']);

    Route::prefix('reviews')->group(function () {
        Route::post('/', [ReviewController::class, 'store']);     
        Route::put('/{uuid}', [ReviewController::class, 'update']);   
        Route::delete('/{uuid}', [ReviewController::class, 'destroy']); 
    });
});