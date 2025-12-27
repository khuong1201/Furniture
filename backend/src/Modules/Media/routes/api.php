<?php

use Illuminate\Support\Facades\Route;
use Modules\Media\Http\Controllers\MediaController;

Route::middleware(['auth:sanctum'])
    ->prefix('admin/media')
    ->group(function () {
        Route::post('/', [MediaController::class, 'store']);
        Route::delete('/{uuid}', [MediaController::class, 'destroy']);
    });