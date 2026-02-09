<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use App\Repositories\CustomerRepository;

class CustomerService extends BaseService
{
    public function __construct(
        private readonly CustomerRepository $customerRepository
    ) {
    }

    /**
     * Найти или создать клиента
     */
    public function findOrCreate(array $data): Customer
    {
        $phone = $data['phone'];
        $email = $data['email'] ?? null;

        // Ищем клиента по телефону или email
        $customer = $this->customerRepository->findByPhoneOrEmail($phone, $email);

        if ($customer !== null) {
            return $customer;
        }

        // Если не найден, создаем нового
        return $this->customerRepository->create([
            'name' => $data['name'],
            'phone' => $phone,
            'email' => $email,
        ]);
    }
}

