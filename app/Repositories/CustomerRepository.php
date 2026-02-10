<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

class CustomerRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Customer());
    }

    /**
     * Найти клиента по телефону
     */
    public function findByPhone(string $phone): ?Customer
    {
        return $this->model->where('phone', $phone)->first();
    }

    /**
     * Найти клиента по email
     */
    public function findByEmail(string $email): ?Customer
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Найти клиента по телефону или email
     */
    public function findByPhoneOrEmail(string $phone, ?string $email = null): ?Customer
    {
        $query = $this->model->where('phone', $phone);

        if ($email !== null) {
            $query->orWhere('email', $email);
        }

        return $query->first();
    }

    /**
     * Фильтрация клиентов
     */
    public function filter(array $filters): Collection
    {
        $query = $this->model->newQuery();

        if (isset($filters['phone'])) {
            $query->where('phone', 'like', '%' . $filters['phone'] . '%');
        }

        if (isset($filters['email'])) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        if (isset($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        return $query->get();
    }
}

