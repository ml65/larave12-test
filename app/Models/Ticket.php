<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'customer_id',
        'subject',
        'text',
        'status',
        'manager_response_date',
    ];

    protected function casts(): array
    {
        return [
            'manager_response_date' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Scope для получения заявок за сегодня
     */
    public function scopeDaily(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope для получения заявок за неделю
     */
    public function scopeWeekly(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    /**
     * Scope для получения заявок за месяц
     */
    public function scopeMonthly(Builder $query): Builder
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }
}
