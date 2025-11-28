<?php

use Illuminate\Support\Facades\Route;
use Modules\Payment\Http\Controllers\PaymentController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::prefix('payments')->middleware([JwtAuthenticate::class])->group(function () {
    Route::get('/', [PaymentController::class, 'payment.index']);
    Route::post('/', [PaymentController::class, 'payment.store']);
    Route::put('/{uuid}', [PaymentController::class, 'payment.update']);
    Route::delete('/{uuid}', [PaymentController::class, 'payment.destroy']);
});
