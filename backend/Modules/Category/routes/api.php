<?php

use Illuminate\Support\Facades\Route;
use Modules\Category\Http\Controllers\CategoryController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])->prefix('admin')->group(function () {
    
    Route::apiResource('categories', CategoryController::class)
        ->parameters(['categories' => 'uuid'])
        ->middleware([]);
});