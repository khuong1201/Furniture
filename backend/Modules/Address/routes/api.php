<?php

use Illuminate\Support\Facades\Route;
use Modules\Address\Http\Controllers\AddressController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])->prefix('addresses')->group(function () {
    
    Route::get('/', [AddressController::class, 'index']);

    Route::post('/', [AddressController::class, 'store']);

    Route::get('/{uuid}', [AddressController::class, 'show']);

    Route::put('/{uuid}', [AddressController::class, 'update']);

    Route::delete('/{uuid}', [AddressController::class, 'destroy']);
});