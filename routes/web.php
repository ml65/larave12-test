<?php

declare(strict_types=1);

use App\Http\Controllers\Web\WidgetController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/widget', [WidgetController::class, 'show'])->name('widget');
