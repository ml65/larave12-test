<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Ticket;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Вычисляем количество новых тикетов и обновляем конфигурацию меню
        try {
            $ticketsCount = Ticket::where('status', Ticket::STATUS_NEW)->count();
        } catch (\Exception $e) {
            $ticketsCount = 0;
        }

        // Обновляем конфигурацию меню AdminLTE
        $menu = config('adminlte.menu', []);

        // Находим элемент меню "tickets" и обновляем его
        foreach ($menu as $key => $item) {
            if (isset($item['text']) && $item['text'] === 'tickets') {
                $menu[$key]['label'] = $ticketsCount;
                $menu[$key]['label_color'] = $ticketsCount > 0 ? 'success' : 'danger';
                break;
            }
        }

        config(['adminlte.menu' => $menu]);
    }
}
