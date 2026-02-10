<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Customer;
use App\Repositories\CustomerRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CustomerRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CustomerRepository();
    }

    #[Test]
    public function it_can_find_customer_by_phone(): void
    {
        $customer = Customer::factory()->create(['phone' => '+79991234567']);

        $found = $this->repository->findByPhone('+79991234567');

        $this->assertNotNull($found);
        $this->assertEquals($customer->id, $found->id);
        $this->assertEquals('+79991234567', $found->phone);
    }

    #[Test]
    public function it_can_find_customer_by_email(): void
    {
        $customer = Customer::factory()->create(['email' => 'test@example.com']);

        $found = $this->repository->findByEmail('test@example.com');

        $this->assertNotNull($found);
        $this->assertEquals($customer->id, $found->id);
        $this->assertEquals('test@example.com', $found->email);
    }

    #[Test]
    public function it_can_find_customer_by_phone_or_email(): void
    {
        $customer = Customer::factory()->create([
            'phone' => '+79991234567',
            'email' => 'test@example.com',
        ]);

        $found = $this->repository->findByPhoneOrEmail('+79991234567');

        $this->assertNotNull($found);
        $this->assertEquals($customer->id, $found->id);
    }

    #[Test]
    public function it_can_find_customer_by_phone_or_email_with_email(): void
    {
        $customer = Customer::factory()->create([
            'phone' => '+79991234567',
            'email' => 'test@example.com',
        ]);

        $found = $this->repository->findByPhoneOrEmail('+79991234568', 'test@example.com');

        $this->assertNotNull($found);
        $this->assertEquals($customer->id, $found->id);
    }

    #[Test]
    public function it_returns_null_when_customer_not_found(): void
    {
        $found = $this->repository->findByPhone('+79999999999');

        $this->assertNull($found);
    }

    #[Test]
    public function it_can_filter_customers_by_phone(): void
    {
        Customer::factory()->create(['phone' => '+79991234567']);
        Customer::factory()->create(['phone' => '+79991234568']);

        $customers = $this->repository->filter(['phone' => '91234567']);

        $this->assertCount(1, $customers);
        $this->assertStringContainsString('91234567', $customers->first()->phone);
    }

    #[Test]
    public function it_can_filter_customers_by_email(): void
    {
        Customer::factory()->create(['email' => 'test1@example.com']);
        Customer::factory()->create(['email' => 'test2@example.com']);

        $customers = $this->repository->filter(['email' => 'test1']);

        $this->assertCount(1, $customers);
        $this->assertStringContainsString('test1', $customers->first()->email);
    }

    #[Test]
    public function it_can_filter_customers_by_name(): void
    {
        Customer::factory()->create(['name' => 'Иван Иванов']);
        Customer::factory()->create(['name' => 'Петр Петров']);

        $customers = $this->repository->filter(['name' => 'Иван']);

        $this->assertCount(1, $customers);
        $this->assertStringContainsString('Иван', $customers->first()->name);
    }
}

