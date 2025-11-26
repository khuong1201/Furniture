<?php

use Illuminate\Support\Facades\Route;
use Modules\Promotion\Http\Controllers\PromotionController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::prefix('promotions')->middleware([JwtAuthenticate::class])->group(function () {
    Route::get('/', [PromotionController::class, 'index']);
    Route::post('/', [PromotionController::class, 'store']);
    Route::put('/{uuid}', [PromotionController::class, 'update']);
    Route::delete('/{uuid}', [PromotionController::class, 'destroy']);
});
