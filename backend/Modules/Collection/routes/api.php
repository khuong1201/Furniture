<?php

use Illuminate\Support\Facades\Route;
use Modules\Collection\Http\Controllers\CollectionController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::prefix('public/collections')->group(function () {
    Route::get('/', [CollectionController::class, 'index']);
    Route::get('/{uuid}', [CollectionController::class, 'show']);
});

Route::middleware(['api', JwtAuthenticate::class])->prefix('admin/collections')->group(function () {
    Route::post('/', [CollectionController::class, 'store'])
        ->middleware('permission:collection.create');

    Route::put('/{uuid}', [CollectionController::class, 'update'])
        ->middleware('permission:collection.edit');

    Route::delete('/{uuid}', [CollectionController::class, 'destroy'])
        ->middleware('permission:collection.delete');
});