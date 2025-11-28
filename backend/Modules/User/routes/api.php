<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UserController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware([JwtAuthenticate::class])
    ->prefix('users')
    ->group(function () {
        Route::get('/', [UserController::class, 'index'])
            ->middleware('permission:user.index')
            ->name('user.index');

        Route::get('/{uuid}', [UserController::class, 'show'])
            ->middleware('permission:user.show')
            ->name('user.show');

        Route::post('/', [UserController::class, 'store'])    
            ->middleware('permission:user.store')
            ->name('user.store');

        Route::put('/{uuid}', [UserController::class, 'update'])  
            ->middleware('permission:user.update')
            ->name('user.update');

        Route::delete('/{uuid}', [UserController::class, 'destroy']) 
            ->middleware('permission:user.destroy')
            ->name('user.destroy');
    });
