<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TicketModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    #[Test]
    public function it_has_correct_status_constants(): void
    {
        $this->assertEquals('new', Ticket::STATUS_NEW);
        $this->assertEquals('in_progress', Ticket::STATUS_IN_PROGRESS);
        $this->assertEquals('completed', Ticket::STATUS_COMPLETED);
    }

    #[Test]
    public function it_returns_status_labels(): void
    {
        $labels = Ticket::getStatusLabels();

        $this->assertIsArray($labels);
        $this->assertEquals('Новая', $labels[Ticket::STATUS_NEW]);
        $this->assertEquals('В работе', $labels[Ticket::STATUS_IN_PROGRESS]);
        $this->assertEquals('Обработана', $labels[Ticket::STATUS_COMPLETED]);
    }

    #[Test]
    public function it_filters_tickets_by_daily_scope(): void
    {
        Ticket::factory()->count(3)->create(['created_at' => now()]);
        Ticket::factory()->count(2)->create(['created_at' => now()->subDay()]);

        $tickets = Ticket::daily()->get();

        $this->assertCount(3, $tickets);
        foreach ($tickets as $ticket) {
            $this->assertTrue($ticket->created_at->isToday());
        }
    }

    #[Test]
    public function it_filters_tickets_by_weekly_scope(): void
    {
        Ticket::factory()->count(5)->create([
            'created_at' => now()->startOfWeek()->addDays(2),
        ]);

        Ticket::factory()->count(2)->create([
            'created_at' => now()->startOfWeek()->subDay(),
        ]);

        $tickets = Ticket::weekly()->get();

        $this->assertCount(5, $tickets);
    }

    #[Test]
    public function it_filters_tickets_by_monthly_scope(): void
    {
        Ticket::factory()->count(7)->create([
            'created_at' => now()->startOfMonth()->addDays(5),
        ]);

        Ticket::factory()->count(3)->create([
            'created_at' => now()->startOfMonth()->subDay(),
        ]);

        $tickets = Ticket::monthly()->get();

        $this->assertCount(7, $tickets);
    }

    #[Test]
    public function it_has_customer_relationship(): void
    {
        $customer = Customer::factory()->create();
        $ticket = Ticket::factory()->create(['customer_id' => $customer->id]);

        $this->assertNotNull($ticket->customer);
        $this->assertEquals($customer->id, $ticket->customer->id);
        $this->assertEquals($customer->name, $ticket->customer->name);
    }

    #[Test]
    public function it_can_attach_media_files(): void
    {
        $ticket = Ticket::factory()->create();
        $file = UploadedFile::fake()->create('test.pdf', 1000);

        $ticket->addMedia($file->getRealPath())
            ->usingName('test.pdf')
            ->usingFileName('test.pdf')
            ->toMediaCollection('attachments');

        $media = $ticket->getMedia('attachments');
        $this->assertCount(1, $media);
        $this->assertEquals('test.pdf', $media->first()->name);
        $this->assertEquals('attachments', $media->first()->collection_name);
    }
}
