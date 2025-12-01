<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\NotificationController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])->group(function () {
    
    Route::get('notifications', [NotificationController::class, 'index']);
    
    Route::patch('notifications/{uuid}/read', [NotificationController::class, 'read']);
    
    Route::post('notifications/read-all', [NotificationController::class, 'readAll']);
    
    Route::delete('notifications/{uuid}', [NotificationController::class, 'destroy']);
    
});