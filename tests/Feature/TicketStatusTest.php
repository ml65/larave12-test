<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TicketStatusTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_update_ticket_status(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => Ticket::STATUS_NEW,
        ]);

        $ticketService = app(TicketService::class);
        $updatedTicket = $ticketService->updateStatus($ticket->id, Ticket::STATUS_IN_PROGRESS);

        $this->assertEquals(Ticket::STATUS_IN_PROGRESS, $updatedTicket->status);
        $this->assertNotNull($updatedTicket->manager_response_date);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => Ticket::STATUS_IN_PROGRESS,
        ]);
    }

    #[Test]
    public function it_validates_status_value(): void
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'manager']);
        $user->assignRole($role);

        $ticket = Ticket::factory()->create();

        $this->actingAs($user);

        $response = $this->putJson("/admin/tickets/{$ticket->id}/status", [
            'status' => 'invalid_status',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    #[Test]
    public function it_sets_manager_response_date_when_status_changed(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => Ticket::STATUS_NEW,
            'manager_response_date' => null,
        ]);

        $ticketService = app(TicketService::class);
        $updatedTicket = $ticketService->updateStatus($ticket->id, Ticket::STATUS_COMPLETED);

        $this->assertNotNull($updatedTicket->manager_response_date);
    }

    #[Test]
    public function it_does_not_set_manager_response_date_for_new_status(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => Ticket::STATUS_IN_PROGRESS,
        ]);

        $ticketService = app(TicketService::class);
        $updatedTicket = $ticketService->updateStatus($ticket->id, Ticket::STATUS_NEW);

        $this->assertNull($updatedTicket->manager_response_date);
    }
}
