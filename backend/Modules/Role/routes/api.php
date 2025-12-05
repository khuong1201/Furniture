<?php

use Illuminate\Support\Facades\Route;
use Modules\Role\Http\Controllers\RoleController;

Route::middleware(['api', 'auth:sanctum'])->prefix('admin')->group(function () {
    Route::apiResource('roles', RoleController::class)->parameters([
        'roles' => 'uuid' 
    ]);
});
