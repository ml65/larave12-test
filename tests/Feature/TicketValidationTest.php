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
        // Создаем клиента и заявку вчера (до начала текущего дня)
        $customer = Customer::factory()->create([
            'phone' => '+79991234567',
        ]);

        Ticket::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => now()->subDay()->endOfDay(), // Вчера в 23:59:59
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

    #[Test]
    public function it_enforces_limit_by_phone_and_email(): void
    {
        // Создаем клиента с телефоном и email
        $customer = Customer::factory()->create([
            'phone' => '+79991234567',
            'email' => 'test@example.com',
        ]);

        // Создаем заявку за сегодня
        Ticket::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => now()->startOfDay()->addHours(10), // Сегодня в 10:00
        ]);

        // Пытаемся создать заявку с тем же телефоном, но другим email
        $data = [
            'name' => 'Test Customer 2',
            'phone' => '+79991234567',
            'email' => 'different@example.com',
            'subject' => 'Test Subject',
            'text' => 'Test message',
        ];

        $response = $this->postJson('/api/tickets', $data);

        $response->assertStatus(429)
            ->assertJson([
                'message' => 'Only one ticket per day is allowed from the same contact',
            ]);

        // Пытаемся создать заявку с тем же email, но другим телефоном
        $data2 = [
            'name' => 'Test Customer 3',
            'phone' => '+79991234568',
            'email' => 'test@example.com',
            'subject' => 'Test Subject',
            'text' => 'Test message',
        ];

        $response2 = $this->postJson('/api/tickets', $data2);

        $response2->assertStatus(429)
            ->assertJson([
                'message' => 'Only one ticket per day is allowed from the same contact',
            ]);
    }

    #[Test]
    public function it_checks_limit_for_full_day_from_00_00_to_23_59(): void
    {
        // Создаем заявку в начале дня (00:00:00)
        $customer = Customer::factory()->create([
            'phone' => '+79991234567',
        ]);

        Ticket::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => now()->startOfDay(), // Сегодня в 00:00:00
        ]);

        // Пытаемся создать заявку в конце дня (23:59:59) - должно быть запрещено
        $data = [
            'name' => 'Test Customer',
            'phone' => '+79991234567',
            'subject' => 'Test Subject',
            'text' => 'Test message',
        ];

        $response = $this->postJson('/api/tickets', $data);

        $response->assertStatus(429);

        // Но заявка вчера в 23:59:59 не должна блокировать
        $customer2 = Customer::factory()->create([
            'phone' => '+79991234568',
        ]);

        Ticket::factory()->create([
            'customer_id' => $customer2->id,
            'created_at' => now()->subDay()->endOfDay(), // Вчера в 23:59:59
        ]);

        $data2 = [
            'name' => 'Test Customer 2',
            'phone' => '+79991234568',
            'subject' => 'Test Subject',
            'text' => 'Test message',
        ];

        $response2 = $this->postJson('/api/tickets', $data2);

        $response2->assertStatus(201);
    }
}

