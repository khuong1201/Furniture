<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UserController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])
    ->prefix('admin') 
    ->group(function () {
        Route::apiResource('users', UserController::class)->parameters([
            'users' => 'uuid'
        ])->middleware('permission:manage_users'); 
    });