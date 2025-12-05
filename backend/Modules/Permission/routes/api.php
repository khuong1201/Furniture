<?php

use Illuminate\Support\Facades\Route;
use Modules\Permission\Http\Controllers\PermissionController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])->prefix('admin')->group(function () {

    Route::get('my-permissions', [PermissionController::class, 'myPermissions']);

    // Allow viewing permissions list
    Route::get('permissions', [PermissionController::class, 'index']);

    // Require manage_permissions for create/update/delete
    Route::apiResource('permissions', PermissionController::class)
        ->parameters(['permissions' => 'uuid'])
        ->except(['index'])
        ->middleware('permission:manage_permissions');
});