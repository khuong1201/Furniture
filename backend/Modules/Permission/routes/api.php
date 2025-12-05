<?php

use Illuminate\Support\Facades\Route;
use Modules\Permission\Http\Controllers\PermissionController;

Route::middleware(['api', "auth:sanctum"])->prefix('admin')->group(function () {
    
    Route::get('my-permissions', [PermissionController::class, 'myPermissions']);

    Route::apiResource('permissions', PermissionController::class)
        ->parameters(['permissions' => 'uuid'])
        ->middleware('permission:manage_permissions');
});