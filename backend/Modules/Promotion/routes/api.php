<?php

use Illuminate\Support\Facades\Route;
use Modules\Promotion\Http\Controllers\PromotionController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])->prefix('admin')->group(function () {
    
    Route::get('promotions', [PromotionController::class, 'index'])
        ->middleware('permission:promotion.view');

    Route::post('promotions', [PromotionController::class, 'store'])
        ->middleware('permission:promotion.create');

    Route::get('promotions/{uuid}', [PromotionController::class, 'show'])
        ->middleware('permission:promotion.view');

    Route::put('promotions/{uuid}', [PromotionController::class, 'update'])
        ->middleware('permission:promotion.edit');

    Route::delete('promotions/{uuid}', [PromotionController::class, 'destroy'])
        ->middleware('permission:promotion.delete');
});