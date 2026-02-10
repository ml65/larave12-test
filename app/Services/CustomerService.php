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
        $phone = $data['phone'] ?? null;
        $email = $data['email'] ?? null;

        if ($phone === null) {
            throw new \InvalidArgumentException('Phone is required');
        }

        // Ищем клиента по телефону или email
        $customer = $this->customerRepository->findByPhoneOrEmail($phone, $email);

        if ($customer !== null) {
            // Обновляем данные если нужно (одно сохранение)
            $updated = false;

            if (isset($data['name']) && $customer->name !== $data['name']) {
                $customer->name = $data['name'];
                $updated = true;
            }

            if (isset($data['email']) && $customer->email !== $data['email']) {
                $customer->email = $data['email'];
                $updated = true;
            }

            if ($updated) {
                $customer->save();
            }

            return $customer;
        }

        // Создаем нового клиента
        return $this->customerRepository->create([
            'name' => $data['name'] ?? '',
            'phone' => $phone,
            'email' => $email,
        ]);
    }
}
