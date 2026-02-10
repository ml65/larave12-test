<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;

// Публичные маршруты
Route::post('/tickets', [TicketController::class, 'store']);

// Авторизация
Route::post('/login', [AuthController::class, 'login']);

// Защищенные маршруты (требуют токен)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/tickets/statistics', [TicketController::class, 'statistics']);
});

