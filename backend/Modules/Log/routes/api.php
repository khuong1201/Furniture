<?php

use Illuminate\Support\Facades\Route;
use Modules\Log\Http\Controllers\LogController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])->prefix('admin/logs')->group(function () {
    
    Route::get('/', [LogController::class, 'index'])
        ->middleware('permission:log.view'); 
        
    Route::get('/{uuid}', [LogController::class, 'show'])
        ->middleware('permission:log.view');
});