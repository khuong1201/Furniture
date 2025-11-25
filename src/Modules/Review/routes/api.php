<?php

use Illuminate\Support\Facades\Route;
use Modules\Review\Http\Controllers\ReviewController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::prefix('reviews')->middleware([JwtAuthenticate::class])->group(function () {
    Route::get('/', [ReviewController::class, 'index']);
    Route::post('/', [ReviewController::class, 'store']);
    Route::put('/{uuid}', [ReviewController::class, 'update']);
    Route::delete('/{uuid}', [ReviewController::class, 'destroy']);
});
