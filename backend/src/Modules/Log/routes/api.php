<?php

use Illuminate\Support\Facades\Route;
use Modules\Log\Http\Controllers\LogController;
use Modules\Log\Domain\Models\Log;

Route::middleware(['auth:sanctum']) 
    ->prefix('admin/logs')
    ->name('admin.logs.')
    ->group(function () {

        Route::get('/', [LogController::class, 'index'])
            ->middleware('can:viewAny,' . Log::class);

        Route::get('/{uuid}', [LogController::class, 'show']);
    });