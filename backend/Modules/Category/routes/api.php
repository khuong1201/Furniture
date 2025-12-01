<?php

use Illuminate\Support\Facades\Route;
use Modules\Category\Http\Controllers\CategoryController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;


Route::middleware(['api', JwtAuthenticate::class])->prefix('admin')->group(function () {
    
    Route::apiResource('categories', CategoryController::class)
        ->parameters(['categories' => 'uuid'])
        ->middleware([
            'permission:manage_categories' 
        ]);
});

Route::prefix('public')->group(function () {
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{uuid}', [CategoryController::class, 'show']);
});