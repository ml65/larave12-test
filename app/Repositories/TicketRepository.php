<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Customer;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
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
        return $this->model->where('customer_id', $customerId)->get();
    }

    /**
     * Найти заявки по статусу
     */
    public function findByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }

    /**
     * Фильтрация заявок
     */
    public function filter(array $filters): Collection
    {
        $query = $this->model->with('customer');
        
        $query = $this->applyFilters($query, $filters);
        
        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Применить фильтры к запросу
     */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $key => $value) {
            match ($key) {
                'status' => $query->where('status', $value),
                'date_from' => $query->whereDate('created_at', '>=', $value),
                'date_to' => $query->whereDate('created_at', '<=', $value),
                'email' => $this->filterByCustomerEmail($query, $value),
                'phone' => $this->filterByCustomerPhone($query, $value),
                default => null,
            };
        }

        return $query;
    }

    /**
     * Фильтр по email клиента
     */
    private function filterByCustomerEmail(Builder $query, string $email): Builder
    {
        $customerIds = Customer::where('email', 'like', '%' . $email . '%')
            ->pluck('id');
        
        return $query->whereIn('customer_id', $customerIds);
    }

    /**
     * Фильтр по телефону клиента
     */
    private function filterByCustomerPhone(Builder $query, string $phone): Builder
    {
        $customerIds = Customer::where('phone', 'like', '%' . $phone . '%')
            ->pluck('id');
        
        return $query->whereIn('customer_id', $customerIds);
    }

    /**
     * Получить количество заявок за день
     */
    public function getDailyCount(): int
    {
        return $this->model->daily()->count();
    }

    /**
     * Получить количество заявок за неделю
     */
    public function getWeeklyCount(): int
    {
        return $this->model->weekly()->count();
    }

    /**
     * Получить количество заявок за месяц
     */
    public function getMonthlyCount(): int
    {
        return $this->model->monthly()->count();
    }
}

