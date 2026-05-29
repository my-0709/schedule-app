<?php

use App\Presentation\Http\Controllers\AuthController;
use App\Presentation\Http\Controllers\ProfileController;
use App\Presentation\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('schedules', ScheduleController::class)->except(['show', 'create', 'edit']);

    Route::prefix('profile')->group(function () {
        Route::put('/', [ProfileController::class, 'update']);
        Route::post('/avatar', [ProfileController::class, 'uploadAvatar']);
        Route::put('/password', [ProfileController::class, 'changePassword']);
        Route::delete('/', [ProfileController::class, 'destroy']);
    });
});
