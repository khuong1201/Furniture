<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AuthApiController;


Route::prefix('auth')->middleware('api')->group(function () {
    Route::post('register', [AuthApiController::class, 'register']);
    Route::post('login', [AuthApiController::class, 'login']);
    Route::post('refresh', [AuthApiController::class, 'refresh']);

    Route::middleware(['auth:sanctum', 'check.token.expiry'])->group(function () {
        Route::get('me', [AuthApiController::class, 'me']);
        Route::post('logout', [AuthApiController::class, 'logout']);
    });
});

