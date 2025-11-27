<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\NotificationController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;
Route::prefix('notifications')->middleware(JwtAuthenticate::class)->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::post('/', [NotificationController::class, 'store']);
    Route::put('/{uuid}', [NotificationController::class, 'update']);
    Route::delete('/{uuid}', [NotificationController::class, 'destroy']);
});