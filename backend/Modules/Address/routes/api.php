<?php

use Illuminate\Support\Facades\Route;
use Modules\Address\Http\Controllers\AddressController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;
Route::middleware(['api', JwtAuthenticate::class])->group(function () {
    Route::apiResource('addresses', AddressController::class)
        ->parameters(['addresses' => 'uuid']);
});