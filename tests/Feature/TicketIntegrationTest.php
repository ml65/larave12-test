<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TicketIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function createManager(): User
    {
        $role = Role::firstOrCreate(['name' => 'manager']);
        $user = User::factory()->create([
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole($role);
        return $user;
    }

    #[Test]
    public function it_completes_full_ticket_creation_flow(): void
    {
        $manager = $this->createManager();

        // 1. Создаем заявку через API с файлами
        $file = UploadedFile::fake()->create('document.pdf', 1000);
        $data = [
            'name' => 'Test Customer',
            'phone' => '+79991234567',
            'email' => 'test@example.com',
            'subject' => 'Integration Test',
            'text' => 'Test message',
            'files' => [$file],
        ];

        $response = $this->postJson('/api/tickets', $data);
        $response->assertStatus(201);

        $json = $response->json();
        $ticketId = $json['id'] ?? $json['data']['id'] ?? null;
        $this->assertNotNull($ticketId);

        // 2. Проверяем, что заявка создана
        $ticket = Ticket::find($ticketId);
        $this->assertNotNull($ticket);
        $this->assertEquals('Integration Test', $ticket->subject);
        $this->assertEquals(Ticket::STATUS_NEW, $ticket->status);

        // 3. Проверяем, что файл прикреплен
        $media = $ticket->getMedia('attachments');
        $this->assertCount(1, $media);

        // 4. Авторизуемся как менеджер
        $this->actingAs($manager);

        // 5. Просматриваем заявку в админ-панели
        $webResponse = $this->get("/admin/tickets/{$ticketId}");
        $webResponse->assertStatus(200);
        $webResponse->assertViewHas('ticket');

        // 6. Изменяем статус заявки
        $updateResponse = $this->put("/admin/tickets/{$ticketId}/status", [
            'status' => Ticket::STATUS_IN_PROGRESS,
        ]);

        $updateResponse->assertRedirect("/admin/tickets/{$ticketId}");
        $updateResponse->assertSessionHas('success');

        // 7. Проверяем, что статус обновлен
        $ticket->refresh();
        $this->assertEquals(Ticket::STATUS_IN_PROGRESS, $ticket->status);
        $this->assertNotNull($ticket->manager_response_date);
    }

    #[Test]
    public function it_handles_ticket_creation_with_existing_customer(): void
    {
        // Создаем существующего клиента
        $existingCustomer = Customer::factory()->create([
            'phone' => '+79991234567',
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        // Создаем заявку с тем же телефоном, но новыми данными
        $data = [
            'name' => 'New Name',
            'phone' => '+79991234567',
            'email' => 'new@example.com',
            'subject' => 'Test Subject',
            'text' => 'Test message',
        ];

        $response = $this->postJson('/api/tickets', $data);
        $response->assertStatus(201);

        // Проверяем, что клиент обновлен, а не создан новый
        $this->assertDatabaseCount('customers', 1);
        
        $existingCustomer->refresh();
        $this->assertEquals('New Name', $existingCustomer->name);
        $this->assertEquals('new@example.com', $existingCustomer->email);

        // Проверяем, что заявка связана с существующим клиентом
        $ticket = Ticket::where('subject', 'Test Subject')->first();
        $this->assertEquals($existingCustomer->id, $ticket->customer_id);
    }

    #[Test]
    public function it_filters_and_displays_tickets_correctly(): void
    {
        $manager = $this->createManager();
        $this->actingAs($manager);

        $customer1 = Customer::factory()->create([
            'email' => 'customer1@example.com',
            'phone' => '+79991234567',
        ]);
        $customer2 = Customer::factory()->create([
            'email' => 'customer2@example.com',
            'phone' => '+79991234568',
        ]);

        // Создаем заявки с разными статусами
        Ticket::factory()->create([
            'customer_id' => $customer1->id,
            'status' => Ticket::STATUS_NEW,
            'created_at' => now(),
        ]);

        Ticket::factory()->create([
            'customer_id' => $customer2->id,
            'status' => Ticket::STATUS_IN_PROGRESS,
            'created_at' => now()->subDays(2),
        ]);

        Ticket::factory()->create([
            'customer_id' => $customer1->id,
            'status' => Ticket::STATUS_COMPLETED,
            'created_at' => now()->subDays(5),
        ]);

        // Фильтруем по статусу
        $response = $this->get('/admin/tickets?status=' . Ticket::STATUS_NEW);
        $response->assertStatus(200);
        $tickets = $response->viewData('tickets');
        $this->assertCount(1, $tickets);
        $this->assertEquals(Ticket::STATUS_NEW, $tickets->first()->status);

        // Фильтруем по email
        $response = $this->get('/admin/tickets?email=customer1@example.com');
        $response->assertStatus(200);
        $tickets = $response->viewData('tickets');
        $this->assertCount(2, $tickets);

        // Фильтруем по телефону
        $response = $this->get('/admin/tickets?phone=91234567');
        $response->assertStatus(200);
        $tickets = $response->viewData('tickets');
        $this->assertCount(2, $tickets);
    }
}

