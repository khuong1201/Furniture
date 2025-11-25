<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UserController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware([JwtAuthenticate::class])->prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{uuid}', [UserController::class, 'show']);
    Route::post('/', [UserController::class, 'store']);
    Route::put('/{uuid}', [UserController::class, 'update']);
    Route::delete('/{uuid}', [UserController::class, 'destroy']);
});

