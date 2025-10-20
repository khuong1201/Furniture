<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UserController;

Route::middleware(['web', 'auth', 'check.role:admin'])->group(function () {
    Route::get('users', [UserController::class, 'index'])
        ->name('users.index')
        ->middleware('check.permission:user.view');

    Route::get('users/create', [UserController::class, 'create'])
        ->name('users.create')
        ->middleware('check.permission:user.create');

    Route::post('users', [UserController::class, 'store'])
        ->name('users.store')
        ->middleware('check.permission:user.create');

    Route::get('users/{user}', [UserController::class, 'show'])
        ->name('users.show')
        ->middleware('check.permission:user.view');

    Route::get('users/{user}/edit', [UserController::class, 'edit'])
        ->name('users.edit')
        ->middleware('check.permission:user.edit');

    Route::put('users/{user}', [UserController::class, 'update'])
        ->name('users.update')
        ->middleware('check.permission:user.edit');

    Route::delete('users/{user}', [UserController::class, 'destroy'])
        ->name('users.destroy')
        ->middleware('check.permission:user.delete');
});

