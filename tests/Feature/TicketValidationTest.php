<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TicketValidationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_validates_phone_format_e164(): void
    {
        $data = [
            'name' => 'Test Customer',
            'phone' => 'invalid-phone', // Неверный формат
            'subject' => 'Test Subject',
            'text' => 'Test message',
        ];

        $response = $this->postJson('/api/tickets', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    #[Test]
    public function it_validates_required_fields(): void
    {
        $response = $this->postJson('/api/tickets', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'phone', 'subject', 'text']);
    }

    #[Test]
    public function it_enforces_one_ticket_per_day_limit(): void
    {
        // Создаем клиента и заявку за сегодня
        $customer = Customer::factory()->create([
            'phone' => '+79991234567',
        ]);

        Ticket::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => now(),
        ]);

        // Пытаемся создать еще одну заявку с тем же телефоном
        $data = [
            'name' => 'Test Customer',
            'phone' => '+79991234567',
            'subject' => 'Test Subject',
            'text' => 'Test message',
        ];

        $response = $this->postJson('/api/tickets', $data);

        $response->assertStatus(429)
            ->assertJson([
                'message' => 'Only one ticket per day is allowed from the same contact',
            ]);
    }

    #[Test]
    public function it_allows_ticket_creation_after_one_day(): void
    {
        // Создаем клиента и заявку вчера
        $customer = Customer::factory()->create([
            'phone' => '+79991234567',
        ]);

        Ticket::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => now()->subDay(),
        ]);

        // Создаем новую заявку сегодня - должно быть разрешено
        $data = [
            'name' => 'Test Customer',
            'phone' => '+79991234567',
            'subject' => 'Test Subject',
            'text' => 'Test message',
        ];

        $response = $this->postJson('/api/tickets', $data);

        $response->assertStatus(201);
    }
}

