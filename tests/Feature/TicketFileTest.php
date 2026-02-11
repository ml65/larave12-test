<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TicketFileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    #[Test]
    public function it_can_create_ticket_with_files(): void
    {
        $customer = Customer::factory()->create([
            'phone' => '+79991234567',
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $data = [
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'subject' => 'Test Subject',
            'text' => 'Test message',
            'files' => [$file],
        ];

        $response = $this->postJson('/api/tickets', $data);

        $response->assertStatus(201);

        $ticket = Ticket::where('subject', 'Test Subject')->first();
        $this->assertNotNull($ticket);

        // Проверяем, что файл прикреплен
        $media = $ticket->getMedia('attachments');
        $this->assertCount(1, $media);
        $this->assertEquals('document.pdf', $media->first()->name);
    }

    #[Test]
    public function it_validates_file_size_limit(): void
    {
        // Создаем файл больше 10MB (лимит указан в StoreTicketRequest как 10240 KB = 10MB)
        $file = UploadedFile::fake()->create('large-file.pdf', 10241); // 10241 KB = больше 10MB

        $data = [
            'name' => 'Test Customer',
            'phone' => '+79991234567',
            'subject' => 'Test Subject',
            'text' => 'Test message',
            'files' => [$file],
        ];

        $response = $this->postJson('/api/tickets', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['files.0']);
    }

    #[Test]
    public function it_can_attach_multiple_files(): void
    {
        $customer = Customer::factory()->create([
            'phone' => '+79991234567',
        ]);

        $file1 = UploadedFile::fake()->create('document1.pdf', 1000);
        $file2 = UploadedFile::fake()->create('document2.jpg', 2000);
        $file3 = UploadedFile::fake()->create('document3.txt', 500);

        $data = [
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'subject' => 'Test Subject',
            'text' => 'Test message',
            'files' => [$file1, $file2, $file3],
        ];

        $response = $this->postJson('/api/tickets', $data);

        $response->assertStatus(201);

        $ticket = Ticket::where('subject', 'Test Subject')->first();
        $this->assertNotNull($ticket);

        // Проверяем, что все файлы прикреплены
        $media = $ticket->getMedia('attachments');
        $this->assertCount(3, $media);

        $fileNames = $media->pluck('name')->toArray();
        $this->assertContains('document1.pdf', $fileNames);
        $this->assertContains('document2.jpg', $fileNames);
        $this->assertContains('document3.txt', $fileNames);
    }

    #[Test]
    public function it_stores_files_in_media_library(): void
    {
        $customer = Customer::factory()->create([
            'phone' => '+79991234567',
        ]);

        $file = UploadedFile::fake()->create('test-file.pdf', 1000, 'application/pdf');

        $data = [
            'name' => $customer->name,
            'phone' => $customer->phone,
            'subject' => 'Test Subject',
            'text' => 'Test message',
            'files' => [$file],
        ];

        $response = $this->postJson('/api/tickets', $data);

        $response->assertStatus(201);

        $ticket = Ticket::where('subject', 'Test Subject')->first();
        $this->assertNotNull($ticket);

        // Проверяем, что файл сохранен через Spatie Media Library
        $media = $ticket->getMedia('attachments');
        $this->assertCount(1, $media);

        $mediaItem = $media->first();
        $this->assertEquals('test-file.pdf', $mediaItem->name);
        $this->assertEquals('attachments', $mediaItem->collection_name);
        $this->assertNotNull($mediaItem->getPath());
    }
}
