<?php

declare(strict_types=1);

use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;

Route::post('/tickets', [TicketController::class, 'store']);

