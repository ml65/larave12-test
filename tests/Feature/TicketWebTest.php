<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TicketWebTest extends TestCase
{
    use RefreshDatabase;

    private function createManager(): User
    {
        $role = Role::firstOrCreate(['name' => 'manager']);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    #[Test]
    public function it_displays_tickets_list_for_manager(): void
    {
        $manager = $this->createManager();
        $customer = Customer::factory()->create();

        Ticket::factory()->count(3)->create([
            'customer_id' => $customer->id,
        ]);

        $this->actingAs($manager);

        $response = $this->get('/admin/tickets');

        $response->assertStatus(200)
            ->assertViewIs('admin.tickets.index')
            ->assertViewHas('tickets')
            ->assertViewHas('statuses')
            ->assertViewHas('filters');

        $tickets = $response->viewData('tickets');
        $this->assertCount(3, $tickets);
    }

    #[Test]
    public function it_filters_tickets_by_status(): void
    {
        $manager = $this->createManager();
        $customer = Customer::factory()->create();

        Ticket::factory()->count(2)->create([
            'customer_id' => $customer->id,
            'status' => Ticket::STATUS_NEW,
        ]);

        Ticket::factory()->count(3)->create([
            'customer_id' => $customer->id,
            'status' => Ticket::STATUS_IN_PROGRESS,
        ]);

        $this->actingAs($manager);

        $response = $this->get('/admin/tickets?status='.Ticket::STATUS_NEW);

        $response->assertStatus(200);
        $tickets = $response->viewData('tickets');
        $this->assertCount(2, $tickets);
        foreach ($tickets as $ticket) {
            $this->assertEquals(Ticket::STATUS_NEW, $ticket->status);
        }
    }

    #[Test]
    public function it_filters_tickets_by_date_range(): void
    {
        $manager = $this->createManager();
        $customer = Customer::factory()->create();

        // Заявка в диапазоне
        Ticket::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => now()->subDays(2)->setTime(12, 0, 0),
        ]);

        // Заявка в диапазоне
        Ticket::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => now()->subDays(4)->setTime(12, 0, 0),
        ]);

        // Заявка вне диапазона (раньше)
        Ticket::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => now()->subDays(6)->setTime(12, 0, 0),
        ]);

        // Заявка вне диапазона (позже)
        Ticket::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => now()->addDay()->setTime(12, 0, 0),
        ]);

        $dateFrom = now()->subDays(5)->format('Y-m-d');
        $dateTo = now()->subDays(1)->format('Y-m-d');

        $this->actingAs($manager);

        $response = $this->get("/admin/tickets?date_from={$dateFrom}&date_to={$dateTo}");

        $response->assertStatus(200);
        $tickets = $response->viewData('tickets');
        $this->assertCount(2, $tickets);
    }

    #[Test]
    public function it_filters_tickets_by_customer_email(): void
    {
        $manager = $this->createManager();

        $customer1 = Customer::factory()->create(['email' => 'test1@example.com']);
        $customer2 = Customer::factory()->create(['email' => 'test2@example.com']);

        Ticket::factory()->create(['customer_id' => $customer1->id]);
        Ticket::factory()->create(['customer_id' => $customer2->id]);

        $this->actingAs($manager);

        $response = $this->get('/admin/tickets?email=test1@example.com');

        $response->assertStatus(200);
        $tickets = $response->viewData('tickets');
        $this->assertCount(1, $tickets);
        $this->assertEquals($customer1->id, $tickets->first()->customer_id);
    }

    #[Test]
    public function it_filters_tickets_by_customer_phone(): void
    {
        $manager = $this->createManager();

        $customer1 = Customer::factory()->create(['phone' => '+79991234567']);
        $customer2 = Customer::factory()->create(['phone' => '+79991234568']);

        Ticket::factory()->create(['customer_id' => $customer1->id]);
        Ticket::factory()->create(['customer_id' => $customer2->id]);

        $this->actingAs($manager);

        $response = $this->get('/admin/tickets?phone=91234567');

        $response->assertStatus(200);
        $tickets = $response->viewData('tickets');
        $this->assertCount(1, $tickets);
        $this->assertEquals($customer1->id, $tickets->first()->customer_id);
    }

    #[Test]
    public function it_displays_ticket_details(): void
    {
        $manager = $this->createManager();
        $customer = Customer::factory()->create();
        $ticket = Ticket::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $this->actingAs($manager);

        $response = $this->get("/admin/tickets/{$ticket->id}");

        $response->assertStatus(200)
            ->assertViewIs('admin.tickets.show')
            ->assertViewHas('ticket')
            ->assertViewHas('files')
            ->assertViewHas('statuses');

        $viewTicket = $response->viewData('ticket');
        $this->assertEquals($ticket->id, $viewTicket->id);
        $this->assertNotNull($viewTicket->customer);
    }

    #[Test]
    public function it_returns_404_for_nonexistent_ticket(): void
    {
        $manager = $this->createManager();

        $this->actingAs($manager);

        $response = $this->get('/admin/tickets/99999');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_update_ticket_status_via_web(): void
    {
        $manager = $this->createManager();
        $ticket = Ticket::factory()->create([
            'status' => Ticket::STATUS_NEW,
        ]);

        $this->actingAs($manager);

        $response = $this->put("/admin/tickets/{$ticket->id}/status", [
            'status' => Ticket::STATUS_IN_PROGRESS,
        ]);

        $response->assertRedirect("/admin/tickets/{$ticket->id}");
        $response->assertSessionHas('success');

        $ticket->refresh();
        $this->assertEquals(Ticket::STATUS_IN_PROGRESS, $ticket->status);
        $this->assertNotNull($ticket->manager_response_date);
    }

    #[Test]
    public function it_shows_error_on_invalid_status_update(): void
    {
        $manager = $this->createManager();
        $ticket = Ticket::factory()->create();

        $this->actingAs($manager);

        $response = $this->put("/admin/tickets/{$ticket->id}/status", [
            'status' => 'invalid_status',
        ]);

        // При ошибке валидации Laravel может редиректить на предыдущую страницу
        $response->assertSessionHasErrors(['status']);
    }
}
