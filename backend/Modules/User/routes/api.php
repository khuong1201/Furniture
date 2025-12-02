<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UserController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])->group(function () {

    Route::get('profile', [UserController::class, 'profile']); 

    Route::prefix('admin/users')->group(function () {
        
        Route::get('/', [UserController::class, 'index'])
            ->middleware('permission:user.view');

        Route::post('/', [UserController::class, 'store'])
            ->middleware('permission:user.create');

        Route::get('/{uuid}', [UserController::class, 'show']);

        Route::put('/{uuid}', [UserController::class, 'update']);

        Route::delete('/{uuid}', [UserController::class, 'destroy'])
             ->middleware('permission:user.delete');
    });
});