<?php

use Illuminate\Support\Facades\Route;
use Modules\Review\Http\Controllers\ReviewController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::prefix('reviews')->middleware([JwtAuthenticate::class])->group(function () {
    Route::post('/', [ReviewController::class, 'store'])
        ->middleware('permission:review.create');

    Route::put('/{uuid}', [ReviewController::class, 'update'])
        ->middleware('permission:review.edit');
        
    Route::delete('/{uuid}', [ReviewController::class, 'destroy'])
        ->middleware('permission:review.delete');
});

Route::prefix('reviews')->group(function () {
    Route::get('/', [ReviewController::class, 'index']);     
    Route::get('/{uuid}', [ReviewController::class, 'show']); 
});