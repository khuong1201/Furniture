<?php

use Illuminate\Support\Facades\Route;
use Modules\Payment\Http\Controllers\PaymentController;

Route::middleware(['auth:sanctum'])
    ->prefix('payments')
    ->group(function () {
        
        Route::get('/', [PaymentController::class, 'index']);

        Route::post('/', [PaymentController::class, 'store']);

        Route::get('/{uuid}', [PaymentController::class, 'show']);

        Route::patch('/{uuid}/status', [PaymentController::class, 'updateStatus']);
        
        Route::put('/{uuid}', [PaymentController::class, 'update']);
    });

Route::prefix('payments')->group(function () {
        Route::post('/callback/{provider}', [PaymentController::class, 'callback']);
    });