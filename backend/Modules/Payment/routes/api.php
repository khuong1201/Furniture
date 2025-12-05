<?php

use Illuminate\Support\Facades\Route;
use Modules\Payment\Http\Controllers\PaymentController;

Route::middleware(['auth:sanctum'])->prefix('payments')->group(function () {
    Route::get('/', [PaymentController::class, 'index']);
    Route::post('/', [PaymentController::class, 'store']);
    Route::get('/{uuid}', [PaymentController::class, 'show']);
    Route::put('/{uuid}', [PaymentController::class, 'update']);
});

Route::post('payments/callback/{provider}', [PaymentController::class, 'callback']);