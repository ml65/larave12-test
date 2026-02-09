<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Создаем роль менеджера
        $managerRole = Role::firstOrCreate(['name' => 'manager']);

        // Создаем менеджера
        $manager = User::factory()->create([
            'name' => 'Manager',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
        ]);
        $manager->assignRole($managerRole);

        // Создаем клиентов
        $customers = Customer::factory()->count(5)->create();

        // Создаем заявки
        foreach ($customers as $customer) {
            // Новая заявка
            Ticket::factory()->create([
                'customer_id' => $customer->id,
                'status' => Ticket::STATUS_NEW,
                'manager_response_date' => null,
            ]);

            // Заявка в работе
            Ticket::factory()->create([
                'customer_id' => $customer->id,
                'status' => Ticket::STATUS_IN_PROGRESS,
                'manager_response_date' => now(),
            ]);

            // Обработанная заявка
            Ticket::factory()->create([
                'customer_id' => $customer->id,
                'status' => Ticket::STATUS_COMPLETED,
                'manager_response_date' => now()->subDays(1),
            ]);
        }
    }
}
