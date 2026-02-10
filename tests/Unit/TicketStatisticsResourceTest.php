<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Resources\TicketStatisticsResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TicketStatisticsResourceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_formats_statistics_data_correctly(): void
    {
        $statistics = [
            'daily' => 5,
            'weekly' => 12,
            'monthly' => 45,
        ];

        $resource = new TicketStatisticsResource($statistics);
        $request = Request::create('/api/tickets/statistics');
        $array = $resource->toArray($request);

        $this->assertEquals(5, $array['daily']);
        $this->assertEquals(12, $array['weekly']);
        $this->assertEquals(45, $array['monthly']);
    }

    #[Test]
    public function it_handles_missing_values(): void
    {
        $statistics = [
            'daily' => 3,
            // weekly и monthly отсутствуют
        ];

        $resource = new TicketStatisticsResource($statistics);
        $request = Request::create('/api/tickets/statistics');
        $array = $resource->toArray($request);

        $this->assertEquals(3, $array['daily']);
        $this->assertEquals(0, $array['weekly']);
        $this->assertEquals(0, $array['monthly']);
    }
}

