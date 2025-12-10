<?php

use Illuminate\Support\Facades\Route;
use Modules\Wishlist\Http\Controllers\WishlistController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['auth:sanctum'])->prefix('wishlist')->group(function () {
    
    Route::get('/', [WishlistController::class, 'index']);

    Route::post('/toggle', [WishlistController::class, 'toggle']);

    Route::delete('/{uuid}', [WishlistController::class, 'destroy']);
});