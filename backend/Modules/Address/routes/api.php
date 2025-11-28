<?php

use Illuminate\Support\Facades\Route;
use Modules\Address\Http\Controllers\AddressController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::prefix('address')
    ->middleware([JwtAuthenticate::class])
    ->group(function () {

        Route::get('/', [AddressController::class, 'index'])
            ->middleware('permission:address.index')
            ->name('address.index');

        Route::post('/', [AddressController::class, 'store'])
            ->middleware('permission:address.store')
            ->name('address.store');

        Route::put('/{uuid}', [AddressController::class, 'update'])
            ->middleware('permission:address.update')
            ->name('address.update');

        Route::delete('/{uuid}', [AddressController::class, 'destroy'])
            ->middleware('permission:address.destroy')
            ->name('address.destroy');
    });