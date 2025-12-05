<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\DashboardController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

Route::middleware(['api', JwtAuthenticate::class])->prefix('admin/dashboard')->group(function () {
    
    Route::get('/summary', [DashboardController::class, 'summary'])
        ->middleware('permission:dashboard.view');

});