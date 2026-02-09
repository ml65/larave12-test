<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketStatisticsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'daily' => $this->resource['daily'] ?? 0,
            'weekly' => $this->resource['weekly'] ?? 0,
            'monthly' => $this->resource['monthly'] ?? 0,
        ];
    }
}
