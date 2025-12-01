<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AuthController;

Route::prefix('auth')->group(function() {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('verify', [AuthController::class, 'verify']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('logout', [AuthController::class, 'logout']);
});
Route::middleware(['jwt'])->get('/protected-route', function () {
    return response()->json([
        'message' => 'Access granted',
        'user' => request()->user() ?? request()->get('auth_user'),
    ]);
});