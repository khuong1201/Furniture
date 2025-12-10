<?php

use Illuminate\Support\Facades\Route;
use Modules\Role\Http\Controllers\RoleController;

Route::middleware(['api', 'auth:sanctum'])
    ->prefix('admin')
    ->group(function () {
        Route::get('roles', [RoleController::class, 'index']);

        Route::post('roles', [RoleController::class, 'store']);

        Route::get('roles/{uuid}', [RoleController::class, 'show']);

        Route::put('roles/{uuid}', [RoleController::class, 'update']);

        Route::patch('roles/{uuid}', [RoleController::class, 'update']);

        Route::delete('roles/{uuid}', [RoleController::class, 'destroy']);
    });
