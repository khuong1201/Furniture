<?php

use Illuminate\Support\Facades\Route;
use Modules\Log\Http\Controllers\LogController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])->prefix('admin')->group(function () {
    Route::get('logs', [LogController::class, 'index'])
        ->middleware('permission:system.view_logs');
        
    Route::get('logs/{uuid}', [LogController::class, 'show'])
        ->middleware('permission:system.view_logs');
});