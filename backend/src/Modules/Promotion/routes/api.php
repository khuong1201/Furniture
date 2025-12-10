<?php

use Illuminate\Support\Facades\Route;
use Modules\Promotion\Http\Controllers\PromotionController;

Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::get('promotions', [PromotionController::class, 'index']);
    Route::post('promotions', [PromotionController::class, 'store']);
    Route::get('promotions/{uuid}', [PromotionController::class, 'show']);
    Route::put('promotions/{uuid}', [PromotionController::class, 'update']);
    Route::delete('promotions/{uuid}', [PromotionController::class, 'destroy']);
});