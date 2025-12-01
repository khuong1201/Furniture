<?php

use Illuminate\Support\Facades\Route;
use Modules\Address\Http\Controllers\AddressController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Chuẩn RESTful: Prefix là danh từ số nhiều (addresses).
| Sử dụng apiResource để tự động map các method index, store, show, update, destroy.
|
*/

Route::middleware(['api', JwtAuthenticate::class])->group(function () {
    Route::apiResource('addresses', AddressController::class)
        ->parameters(['addresses' => 'uuid']);
});