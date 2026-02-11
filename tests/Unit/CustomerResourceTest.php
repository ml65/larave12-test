<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerResourceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_formats_customer_data_correctly(): void
    {
        $customer = Customer::factory()->create([
            'name' => 'Test Customer',
            'phone' => '+79991234567',
            'email' => 'test@example.com',
        ]);

        $resource = new CustomerResource($customer);
        $request = Request::create('/api/tickets');
        $array = $resource->toArray($request);

        $this->assertEquals($customer->id, $array['id']);
        $this->assertEquals('Test Customer', $array['name']);
        $this->assertEquals('+79991234567', $array['phone']);
        $this->assertEquals('test@example.com', $array['email']);
    }
}
