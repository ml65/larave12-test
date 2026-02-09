<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Customer;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;

class TicketRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Ticket());
    }

    /**
     * Найти заявки по ID клиента
     */
    public function findByCustomerId(int $customerId): Collection
    {
        return Ticket::where('customer_id', $customerId)->get();
    }

    /**
     * Найти заявки по статусу
     */
    public function findByStatus(string $status): Collection
    {
        return Ticket::where('status', $status)->get();
    }

    /**
     * Фильтрация заявок
     */
    public function filter(array $filters): Collection
    {
        $query = Ticket::with('customer');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['email'])) {
            $customerIds = Customer::where('email', 'like', '%' . $filters['email'] . '%')
                ->pluck('id');
            $query->whereIn('customer_id', $customerIds);
        }

        if (isset($filters['phone'])) {
            $customerIds = Customer::where('phone', 'like', '%' . $filters['phone'] . '%')
                ->pluck('id');
            $query->whereIn('customer_id', $customerIds);
        }

        return $query->get();
    }

    /**
     * Получить количество заявок за день
     */
    public function getDailyCount(): int
    {
        return Ticket::daily()->count();
    }

    /**
     * Получить количество заявок за неделю
     */
    public function getWeeklyCount(): int
    {
        return Ticket::weekly()->count();
    }

    /**
     * Получить количество заявок за месяц
     */
    public function getMonthlyCount(): int
    {
        return Ticket::monthly()->count();
    }
}

