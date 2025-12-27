<?php

use Illuminate\Support\Facades\Route;
use Modules\Brand\Http\Controllers\BrandController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('brands', BrandController::class)->names('brand');
});
