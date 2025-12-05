<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UserController;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('profile', [UserController::class, 'profile']); 

    Route::prefix('admin/users')->group(function () {
        
        Route::get('/', [UserController::class, 'index'])
            ->middleware('can:viewAny,' . \Modules\User\Domain\Models\User::class);

        Route::post('/', [UserController::class, 'store'])
            ->middleware('can:create,' . \Modules\User\Domain\Models\User::class);

        Route::get('/{uuid}', [UserController::class, 'show']);

        Route::put('/{uuid}', [UserController::class, 'update']);

        Route::delete('/{uuid}', [UserController::class, 'destroy']);
    });
});