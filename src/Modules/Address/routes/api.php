<?php

use Illuminate\Support\Facades\Route;
use Modules\Address\Http\Controllers\AddressController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::prefix('address')->middleware([JwtAuthenticate::class])->group(function () {
    Route::get('/', [AddressController::class, 'index']);
    Route::post('/', [AddressController::class, 'store']);
    Route::put('/{uuid}', [AddressController::class, 'update']);
    Route::delete('/{uuid}', [AddressController::class, 'destroy']);
});
