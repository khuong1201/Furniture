<?php

use Illuminate\Support\Facades\Route;
use Modules\Category\Http\Controllers\CategoryController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::prefix('public/categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{uuid}', [CategoryController::class, 'show']);
});

Route::middleware(['api', JwtAuthenticate::class])->prefix('admin/categories')->group(function () {

    Route::post('/', [CategoryController::class, 'store'])
        ->middleware('permission:category.create');

    Route::put('/{uuid}', [CategoryController::class, 'update'])
        ->middleware('permission:category.edit');

    Route::delete('/{uuid}', [CategoryController::class, 'destroy'])
        ->middleware('permission:category.delete');
});