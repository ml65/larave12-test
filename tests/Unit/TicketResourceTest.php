<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Resources\TicketResource;
use App\Models\Customer;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TicketResourceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_formats_ticket_data_correctly(): void
    {
        $customer = Customer::factory()->create();
        $ticket = Ticket::factory()->create([
            'customer_id' => $customer->id,
            'subject' => 'Test Subject',
            'text' => 'Test Text',
            'status' => Ticket::STATUS_NEW,
        ]);

        $resource = new TicketResource($ticket);
        $request = Request::create('/api/tickets');
        $array = $resource->toArray($request);

        $this->assertEquals($ticket->id, $array['id']);
        $this->assertEquals('Test Subject', $array['subject']);
        $this->assertEquals('Test Text', $array['text']);
        $this->assertEquals(Ticket::STATUS_NEW, $array['status']);
        $this->assertArrayHasKey('created_at', $array);
    }

    #[Test]
    public function it_includes_customer_when_loaded(): void
    {
        $customer = Customer::factory()->create();
        $ticket = Ticket::factory()->create(['customer_id' => $customer->id]);
        $ticket->load('customer');

        $resource = new TicketResource($ticket);
        $request = Request::create('/api/tickets');
        $array = $resource->toArray($request);

        $this->assertArrayHasKey('customer', $array);
        $this->assertNotNull($array['customer']);
        $this->assertEquals($customer->id, $array['customer']['id']);
    }

    #[Test]
    public function it_formats_created_at_as_iso_string(): void
    {
        $ticket = Ticket::factory()->create();

        $resource = new TicketResource($ticket);
        $request = Request::create('/api/tickets');
        $array = $resource->toArray($request);

        $this->assertArrayHasKey('created_at', $array);
        $this->assertIsString($array['created_at']);
        // Проверяем формат ISO 8601
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $array['created_at']);
    }
}

