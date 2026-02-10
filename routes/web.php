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
Route::get('/widget-test', function () {
    return view('widget-test');
})->name('widget-test');

// Авторизация
Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/admin/login', [AuthController::class, 'login']);
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('logout');

// Админ-панель (только для менеджеров)
Route::middleware([EnsureUserIsManager::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.tickets.index');
    });
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{id}', [TicketController::class, 'show'])->name('tickets.show');
    Route::put('/tickets/{id}/status', [TicketController::class, 'updateStatus'])->name('tickets.updateStatus');
    // Редирект всех остальных запросов на /admin/tickets
    Route::any('{any}', function () {
        return redirect()->route('admin.tickets.index');
    })->where('any', '.*');
});
