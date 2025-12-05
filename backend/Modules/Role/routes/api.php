<?php

use Illuminate\Support\Facades\Route;
use Modules\Role\Http\Controllers\RoleController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])->prefix('admin')->group(function () {
    Route::apiResource('roles', RoleController::class)->parameters([
        'roles' => 'uuid'
    ]);
});
