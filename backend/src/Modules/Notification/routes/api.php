<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\NotificationController;

Route::middleware(['auth:sanctum'])
    ->prefix('notifications')
    ->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/read-all', [NotificationController::class, 'readAll']);
        Route::patch('/{uuid}/read', [NotificationController::class, 'read']);
        Route::delete('/{uuid}', [NotificationController::class, 'destroy']);
    });