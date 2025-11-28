<?php

use Illuminate\Support\Facades\Route;
use Modules\Role\Http\Controllers\RoleController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;
Route::prefix('admin')->middleware([JwtAuthenticate::class, 'permission:manage_roles'])->group(function () {
    Route::get('roles', [RoleController::class, 'role.index']);
    Route::post('roles', [RoleController::class, 'role.store']);
    Route::get('roles/{role}', [RoleController::class, 'role.show']);
    Route::put('roles/{role}', [RoleController::class, 'role.update']);
    Route::delete('roles/{role}', [RoleController::class, 'role.destroy']);
});
