<?php

use Illuminate\Support\Facades\Route;
use Modules\Log\Http\Controllers\LogController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('logs', LogController::class)->names('log');
});
