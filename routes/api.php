<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->middleware(['api', 'token.checker'])->group(function () {
    Route::get('/me', [AuthController::class, 'getUserProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/token/refresh', [AuthController::class, 'refreshToken'])
        ->withoutMiddleware(['token.checker']);
    Route::post('/login', [AuthController::class, 'authenticate'])
        ->withoutMiddleware(['token.checker'])->middleware('auth.basic.login');
});
