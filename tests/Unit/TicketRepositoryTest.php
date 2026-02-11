<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Ticket;
use App\Repositories\TicketRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TicketRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private TicketRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TicketRepository;
    }

    #[Test]
    public function it_can_find_tickets_by_customer_id(): void
    {
        $customer1 = Customer::factory()->create();
        $customer2 = Customer::factory()->create();

        Ticket::factory()->count(3)->create(['customer_id' => $customer1->id]);
        Ticket::factory()->count(2)->create(['customer_id' => $customer2->id]);

        $tickets = $this->repository->findByCustomerId($customer1->id);

        $this->assertCount(3, $tickets);
        foreach ($tickets as $ticket) {
            $this->assertEquals($customer1->id, $ticket->customer_id);
        }
    }

    #[Test]
    public function it_can_find_tickets_by_status(): void
    {
        Ticket::factory()->count(3)->create(['status' => Ticket::STATUS_NEW]);
        Ticket::factory()->count(2)->create(['status' => Ticket::STATUS_IN_PROGRESS]);

        $tickets = $this->repository->findByStatus(Ticket::STATUS_NEW);

        $this->assertCount(3, $tickets);
        foreach ($tickets as $ticket) {
            $this->assertEquals(Ticket::STATUS_NEW, $ticket->status);
        }
    }

    #[Test]
    public function it_can_filter_tickets_by_status(): void
    {
        Ticket::factory()->count(2)->create(['status' => Ticket::STATUS_NEW]);
        Ticket::factory()->count(3)->create(['status' => Ticket::STATUS_IN_PROGRESS]);

        $tickets = $this->repository->filter(['status' => Ticket::STATUS_NEW]);

        $this->assertCount(2, $tickets);
        foreach ($tickets as $ticket) {
            $this->assertEquals(Ticket::STATUS_NEW, $ticket->status);
        }
    }

    #[Test]
    public function it_can_filter_tickets_by_date_from(): void
    {
        // Заявки после dateFrom
        Ticket::factory()->create(['created_at' => now()->subDays(1)->setTime(12, 0, 0)]);
        Ticket::factory()->create(['created_at' => now()->subDays(2)->setTime(12, 0, 0)]);
        // Заявка до dateFrom
        Ticket::factory()->create(['created_at' => now()->subDays(5)->setTime(12, 0, 0)]);

        $dateFrom = now()->subDays(3)->format('Y-m-d');
        $tickets = $this->repository->filter(['date_from' => $dateFrom]);

        // Должны быть заявки от dateFrom и позже (1 и 2 дня назад)
        $this->assertCount(2, $tickets);
    }

    #[Test]
    public function it_can_filter_tickets_by_date_to(): void
    {
        Ticket::factory()->create(['created_at' => now()->subDays(5)]);
        Ticket::factory()->create(['created_at' => now()->subDays(2)]);
        Ticket::factory()->create(['created_at' => now()->subDays(10)]);

        $dateTo = now()->subDays(3)->format('Y-m-d');
        $tickets = $this->repository->filter(['date_to' => $dateTo]);

        $this->assertCount(2, $tickets);
    }

    #[Test]
    public function it_can_filter_tickets_by_customer_email(): void
    {
        $customer1 = Customer::factory()->create(['email' => 'test1@example.com']);
        $customer2 = Customer::factory()->create(['email' => 'test2@example.com']);

        Ticket::factory()->create(['customer_id' => $customer1->id]);
        Ticket::factory()->create(['customer_id' => $customer2->id]);

        $tickets = $this->repository->filter(['email' => 'test1@example.com']);

        $this->assertCount(1, $tickets);
        $this->assertEquals($customer1->id, $tickets->first()->customer_id);
    }

    #[Test]
    public function it_can_filter_tickets_by_customer_phone(): void
    {
        $customer1 = Customer::factory()->create(['phone' => '+79991234567']);
        $customer2 = Customer::factory()->create(['phone' => '+79991234568']);

        Ticket::factory()->create(['customer_id' => $customer1->id]);
        Ticket::factory()->create(['customer_id' => $customer2->id]);

        $tickets = $this->repository->filter(['phone' => '91234567']);

        $this->assertCount(1, $tickets);
        $this->assertEquals($customer1->id, $tickets->first()->customer_id);
    }

    #[Test]
    public function it_returns_daily_count(): void
    {
        Ticket::factory()->count(5)->create(['created_at' => now()]);
        Ticket::factory()->count(3)->create(['created_at' => now()->subDay()]);

        $count = $this->repository->getDailyCount();

        $this->assertEquals(5, $count);
    }

    #[Test]
    public function it_returns_weekly_count(): void
    {
        Ticket::factory()->count(7)->create([
            'created_at' => now()->startOfWeek()->addDays(2),
        ]);

        Ticket::factory()->count(3)->create([
            'created_at' => now()->startOfWeek()->subDay(),
        ]);

        $count = $this->repository->getWeeklyCount();

        $this->assertEquals(7, $count);
    }

    #[Test]
    public function it_returns_monthly_count(): void
    {
        Ticket::factory()->count(10)->create([
            'created_at' => now()->startOfMonth()->addDays(5),
        ]);

        Ticket::factory()->count(5)->create([
            'created_at' => now()->startOfMonth()->subDay(),
        ]);

        $count = $this->repository->getMonthlyCount();

        $this->assertEquals(10, $count);
    }
}
