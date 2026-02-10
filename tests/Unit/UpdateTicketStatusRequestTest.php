<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Requests\UpdateTicketStatusRequest;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateTicketStatusRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_validates_status_is_required(): void
    {
        $request = new UpdateTicketStatusRequest();
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    #[Test]
    public function it_validates_status_is_valid_value(): void
    {
        $request = new UpdateTicketStatusRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'status' => Ticket::STATUS_NEW,
        ], $rules);

        $this->assertFalse($validator->fails());
    }

    #[Test]
    public function it_rejects_invalid_status(): void
    {
        $request = new UpdateTicketStatusRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'status' => 'invalid_status',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    #[Test]
    public function it_accepts_all_valid_statuses(): void
    {
        $request = new UpdateTicketStatusRequest();
        $rules = $request->rules();

        $validStatuses = [
            Ticket::STATUS_NEW,
            Ticket::STATUS_IN_PROGRESS,
            Ticket::STATUS_COMPLETED,
        ];

        foreach ($validStatuses as $status) {
            $validator = Validator::make(['status' => $status], $rules);
            $this->assertFalse($validator->fails(), "Status {$status} should be valid");
        }
    }
}

