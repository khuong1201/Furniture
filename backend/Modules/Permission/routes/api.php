<?php

use Illuminate\Support\Facades\Route;
use Modules\Permission\Http\Controllers\PermissionController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::prefix('api/admin')->middleware([JwtAuthenticate::class, 'permission:manage_permissions'])->group(function () {
    Route::get('permissions', [PermissionController::class, 'index'])->name('permission.index');
    Route::post('permissions', [PermissionController::class, 'store'])->name('permission.store');
    Route::get('permissions/{permission}', [PermissionController::class, 'show'])->name('permission.show');
});
