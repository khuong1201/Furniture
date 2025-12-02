<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UserController;
use Modules\Auth\Http\Middleware\JwtAuthenticate;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Đường dẫn mặc định: /api/admin/users
| Middleware: JwtAuthenticate (Yêu cầu đăng nhập)
| Authorization: Đã được xử lý trong UserController bằng Policy.
|
*/

Route::middleware(['api', JwtAuthenticate::class])->group(function () {

    Route::prefix('users')->group(function () {
        
        Route::get('/', [UserController::class, 'index']);

        Route::post('/', [UserController::class, 'store']);

        Route::get('/{uuid}', [UserController::class, 'show']);

        Route::put('/{uuid}', [UserController::class, 'update']);

        Route::delete('/{uuid}', [UserController::class, 'destroy']);
    });
});