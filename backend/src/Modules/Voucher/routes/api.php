<?php

use Illuminate\Support\Facades\Route;
use Modules\Voucher\Http\Controllers\VoucherController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['auth:sanctum'])->prefix('admin/vouchers')->group(function () {
    Route::get('/', [VoucherController::class, 'index']);
    Route::post('/', [VoucherController::class, 'store']);
    Route::get('/{uuid}', [VoucherController::class, 'show']);
    Route::put('/{uuid}', [VoucherController::class, 'update']);
    Route::delete('/{uuid}', [VoucherController::class, 'destroy']);
});