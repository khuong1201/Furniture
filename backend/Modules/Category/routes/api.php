<?php

use Illuminate\Support\Facades\Route;
use Modules\Category\Http\Controllers\CategoryController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;
Route::middleware([JwtAuthenticate::class])
    ->prefix('categories')
    ->group(function () {
        Route::get('/', [CategoryController::class, 'category.index']);
        Route::post('/', [CategoryController::class, 'category.store']);
        Route::put('/{uuid}', [CategoryController::class, 'category.update']);
        Route::delete('/{uuid}', [CategoryController::class, 'category.destroy']);
    });
