<?php

use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('v1')->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('api.orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('api.orders.show');
});
