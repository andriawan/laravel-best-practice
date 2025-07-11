<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->middleware(['force.json.response', 'api', 'token.checker', 'throttle:api'])->group(function () {
    Route::get('/me', [AuthController::class, 'getUserProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/token/refresh', [AuthController::class, 'refreshToken'])
        ->withoutMiddleware(['token.checker']);
    Route::post('/token/blacklist', [AuthController::class, 'blacklistTokenForUser']);
    Route::post('/login', [AuthController::class, 'authenticate'])
        ->withoutMiddleware(['token.checker'])->middleware('auth.basic.login');
});
