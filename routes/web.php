<?php

declare(strict_types=1);

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\TicketController;
use App\Http\Controllers\Web\WidgetController;
use App\Http\Middleware\EnsureUserIsManager;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/widget', [WidgetController::class, 'show'])->name('widget');

// Авторизация
Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/admin/login', [AuthController::class, 'login']);
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('logout');

// Админ-панель (только для менеджеров)
Route::middleware([EnsureUserIsManager::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
});
