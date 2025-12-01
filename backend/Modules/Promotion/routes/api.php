<?php

use Illuminate\Support\Facades\Route;
use Modules\Promotion\Http\Controllers\PromotionController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])->prefix('admin')->group(function () {
    
    Route::apiResource('promotions', PromotionController::class)
        ->parameters(['promotions' => 'uuid']);
        // ->middleware([
        //     // 'index' => 'permission:promotion.view',
        //     // 'store' => 'permission:promotion.create',
        //     // ...
        // ]);
});