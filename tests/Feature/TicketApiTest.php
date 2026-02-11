<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TicketApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_ticket_via_api(): void
    {
        $data = [
            'name' => 'Test Customer',
            'phone' => '+79991234567',
            'email' => 'test@example.com',
            'subject' => 'Test Subject',
            'text' => 'Test message text',
        ];

        $response = $this->postJson('/api/tickets', $data);

        $response->assertStatus(201);

        $json = $response->json();

        // Проверяем структуру ответа (может быть обернут в 'data' или нет)
        if (isset($json['data'])) {
            $response->assertJsonStructure([
                'data' => [
                    'id',
                    'subject',
                    'text',
                    'status',
                    'customer',
                    'created_at',
                ],
            ])->assertJson([
                'data' => [
                    'subject' => 'Test Subject',
                    'status' => Ticket::STATUS_NEW,
                ],
            ]);
        } else {
            $response->assertJsonStructure([
                'id',
                'subject',
                'text',
                'status',
                'customer',
                'created_at',
            ])->assertJson([
                'subject' => 'Test Subject',
                'status' => Ticket::STATUS_NEW,
            ]);
        }

        $this->assertDatabaseHas('tickets', [
            'subject' => 'Test Subject',
            'status' => Ticket::STATUS_NEW,
        ]);

        $this->assertDatabaseHas('customers', [
            'phone' => '+79991234567',
            'email' => 'test@example.com',
        ]);
    }

    #[Test]
    public function it_creates_customer_when_ticket_is_created(): void
    {
        $data = [
            'name' => 'New Customer',
            'phone' => '+79991234568',
            'subject' => 'Test Subject',
            'text' => 'Test message',
        ];

        $this->postJson('/api/tickets', $data);

        $this->assertDatabaseHas('customers', [
            'name' => 'New Customer',
            'phone' => '+79991234568',
        ]);
    }
}
