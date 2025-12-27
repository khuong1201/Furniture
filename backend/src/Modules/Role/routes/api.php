<?php

use Illuminate\Support\Facades\Route;
use Modules\Role\Http\Controllers\RoleController;

Route::middleware(['api', 'auth:sanctum'])->prefix('admin')->group(function () {
    
    Route::prefix('roles')->group(function () {
        
        Route::get('all', [RoleController::class, 'all']); 

        Route::get('/', [RoleController::class, 'index']);
        Route::post('/', [RoleController::class, 'store']);
        Route::get('/{uuid}', [RoleController::class, 'show']);
        Route::put('/{uuid}', [RoleController::class, 'update']);
        Route::delete('/{uuid}', [RoleController::class, 'destroy']);
    });
});