<?php

use Illuminate\Support\Facades\Route;
use Modules\Log\Http\Controllers\LogController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;
use Modules\Permission\Http\Middleware\CheckPermission;

Route::prefix('logs')->middleware([JwtAuthenticate::class, CheckPermission::class . ':view_logs'])->group(function () {
    Route::get('/', [LogController::class, 'index']);
});

