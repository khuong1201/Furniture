<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\DashboardController;

Route::middleware(['api', "auth:sanctum"])->prefix('admin/dashboard')->group(function () {

    Route::get('/summary', [DashboardController::class, 'summary'])->middleware([]);
});