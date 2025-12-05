<?php

use Illuminate\Support\Facades\Route;
use Modules\Currency\Http\Controllers\CurrencyController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

// Public
Route::get('public/currencies', [CurrencyController::class, 'getActive']);

// Admin
Route::middleware(['auth:sanctum'])->prefix('admin/currencies')->group(function () {
    Route::get('/', [CurrencyController::class, 'index']);
    Route::post('/', [CurrencyController::class, 'store']);
    Route::put('/{uuid}', [CurrencyController::class, 'update']);
    Route::delete('/{uuid}', [CurrencyController::class, 'destroy']);
});