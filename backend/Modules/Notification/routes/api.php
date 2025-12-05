<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\NotificationController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['auth:sanctum'])->prefix('notifications')->group(function () {
    
    Route::get('/', [NotificationController::class, 'index']);

    Route::patch('/{uuid}/read', [NotificationController::class, 'read']);

    Route::post('/read-all', [NotificationController::class, 'readAll']);

    Route::delete('/{uuid}', [NotificationController::class, 'destroy']);
});