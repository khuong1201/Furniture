<?php

use Illuminate\Support\Facades\Route;
use Modules\Category\Http\Controllers\CategoryController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;
Route::middleware([JwtAuthenticate::class])
    ->prefix('categories')
    ->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::put('/{uuid}', [CategoryController::class, 'update']);
        Route::delete('/{uuid}', [CategoryController::class, 'destroy']);
    });
