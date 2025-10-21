<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UserApiController;

Route::middleware(['auth:sanctum', 'check.role:customer,admin'])->group(function () {
    Route::get('users', [UserApiController::class, 'index'])
        ->middleware(['check.permission:user.view', 'check.role:admin']);

    Route::post('users', [UserApiController::class, 'store'])
        ->middleware('check.permission:user.create');

    Route::get('users/{user}', [UserApiController::class, 'show'])
        ->middleware('check.permission:user.view');

    Route::put('users/{user}', [UserApiController::class, 'update'])
        ->middleware('check.permission:user.edit');

    Route::delete('users/{user}', [UserApiController::class, 'destroy'])
        ->middleware('check.permission:user.delete');
});

