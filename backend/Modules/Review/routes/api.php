<?php

use Illuminate\Support\Facades\Route;
use Modules\Review\Http\Controllers\ReviewController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::prefix('reviews')->middleware([JwtAuthenticate::class])->group(function () {
    Route::get('/', [ReviewController::class, 'review.index']);
    Route::post('/', [ReviewController::class, 'review.store']);
    Route::put('/{uuid}', [ReviewController::class, 'review.update']);
    Route::delete('/{uuid}', [ReviewController::class, 'review.destroy']);
});
