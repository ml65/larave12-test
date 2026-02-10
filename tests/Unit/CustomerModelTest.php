<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_tickets_relationship(): void
    {
        $customer = Customer::factory()->create();
        $ticket1 = Ticket::factory()->create(['customer_id' => $customer->id]);
        $ticket2 = Ticket::factory()->create(['customer_id' => $customer->id]);

        $tickets = $customer->tickets;

        $this->assertCount(2, $tickets);
        $this->assertTrue($tickets->contains($ticket1));
        $this->assertTrue($tickets->contains($ticket2));
    }

    #[Test]
    public function it_can_have_multiple_tickets(): void
    {
        $customer = Customer::factory()->create();

        Ticket::factory()->count(5)->create(['customer_id' => $customer->id]);

        $this->assertCount(5, $customer->tickets);
    }
}

