<?php

use Illuminate\Support\Facades\Route;
use Modules\Payment\Http\Controllers\PaymentController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::prefix('payments')->middleware([JwtAuthenticate::class])->group(function () {
    Route::get('/', [PaymentController::class, 'index']);
    
    Route::post('/', [PaymentController::class, 'store']);
    
    Route::put('/{uuid}', [PaymentController::class, 'update']);
});

Route::post('payments/callback/{provider}', [PaymentController::class, 'callback']);