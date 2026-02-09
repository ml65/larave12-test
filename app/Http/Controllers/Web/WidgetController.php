<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class WidgetController extends Controller
{
    /**
     * Отобразить виджет обратной связи
     */
    public function show(): View
    {
        return view('widget');
    }
}
