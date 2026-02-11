<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerServiceTest extends TestCase
{
    use RefreshDatabase;

    private CustomerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CustomerService::class);
    }

    #[Test]
    public function it_creates_new_customer_when_not_exists(): void
    {
        $data = [
            'name' => 'New Customer',
            'phone' => '+79991234567',
            'email' => 'new@example.com',
        ];

        $customer = $this->service->findOrCreate($data);

        $this->assertNotNull($customer);
        $this->assertEquals('New Customer', $customer->name);
        $this->assertEquals('+79991234567', $customer->phone);
        $this->assertEquals('new@example.com', $customer->email);

        $this->assertDatabaseHas('customers', [
            'phone' => '+79991234567',
            'email' => 'new@example.com',
        ]);
    }

    #[Test]
    public function it_returns_existing_customer_by_phone(): void
    {
        $existing = Customer::factory()->create([
            'phone' => '+79991234567',
            'name' => 'Existing Customer',
        ]);

        $data = [
            'name' => 'Updated Name',
            'phone' => '+79991234567',
            'email' => 'new@example.com',
        ];

        $customer = $this->service->findOrCreate($data);

        $this->assertEquals($existing->id, $customer->id);
        $this->assertEquals('+79991234567', $customer->phone);
    }

    #[Test]
    public function it_returns_existing_customer_by_email(): void
    {
        $existing = Customer::factory()->create([
            'email' => 'existing@example.com',
            'phone' => '+79991234567',
        ]);

        $data = [
            'name' => 'New Name',
            'phone' => '+79991234568',
            'email' => 'existing@example.com',
        ];

        $customer = $this->service->findOrCreate($data);

        $this->assertEquals($existing->id, $customer->id);
    }

    #[Test]
    public function it_updates_customer_name_when_changed(): void
    {
        $existing = Customer::factory()->create([
            'phone' => '+79991234567',
            'name' => 'Old Name',
        ]);

        $data = [
            'name' => 'New Name',
            'phone' => '+79991234567',
        ];

        $customer = $this->service->findOrCreate($data);

        $this->assertEquals($existing->id, $customer->id);
        $this->assertEquals('New Name', $customer->name);

        $customer->refresh();
        $this->assertEquals('New Name', $customer->name);
    }

    #[Test]
    public function it_updates_customer_email_when_changed(): void
    {
        $existing = Customer::factory()->create([
            'phone' => '+79991234567',
            'email' => 'old@example.com',
        ]);

        $data = [
            'name' => $existing->name,
            'phone' => '+79991234567',
            'email' => 'new@example.com',
        ];

        $customer = $this->service->findOrCreate($data);

        $this->assertEquals($existing->id, $customer->id);
        $this->assertEquals('new@example.com', $customer->email);

        $customer->refresh();
        $this->assertEquals('new@example.com', $customer->email);
    }

    #[Test]
    public function it_throws_exception_when_phone_missing(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Phone is required');

        $data = [
            'name' => 'Customer',
            'email' => 'test@example.com',
        ];

        $this->service->findOrCreate($data);
    }
}
