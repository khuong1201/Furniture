<?php

use Illuminate\Support\Facades\Route;
use Modules\Permission\Http\Controllers\PermissionController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', 'auth:api'])->prefix('admin')->group(function () {
    
    Route::get('my-permissions', [PermissionController::class, 'myPermissions']);

    Route::apiResource('permissions', PermissionController::class)
        ->parameters(['permissions' => 'uuid'])
        ->middleware('permission:manage_permissions');
});