<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = [
            Ticket::STATUS_NEW,
            Ticket::STATUS_IN_PROGRESS,
            Ticket::STATUS_COMPLETED,
        ];

        return [
            'customer_id' => Customer::factory(),
            'subject' => $this->faker->sentence(3),
            'text' => $this->faker->paragraph(3),
            'status' => $this->faker->randomElement($statuses),
            'manager_response_date' => $this->faker->optional()->dateTime(),
        ];
    }
}
