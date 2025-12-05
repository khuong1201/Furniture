<?php

use Illuminate\Support\Facades\Route;
use Modules\Media\Http\Controllers\MediaController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['auth:sanctum'])->prefix('admin/media')->group(function () {
    
    Route::post('/', [MediaController::class, 'store'])
        ->middleware('permission:media.create'); 

    Route::delete('/{uuid}', [MediaController::class, 'destroy']);
});