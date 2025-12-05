<?php

use Illuminate\Support\Facades\Route;
use Modules\Log\Http\Controllers\LogController;

Route::middleware(['auth:sanctum'])->prefix('admin/logs')->group(function () {
    
    Route::get('/', [LogController::class, 'index'])
        ->middleware('can:viewAny,' . \Modules\Log\Domain\Models\Log::class); 
        
    Route::get('/{uuid}', [LogController::class, 'show'])
        ->middleware('can:view,uuid'); 
});