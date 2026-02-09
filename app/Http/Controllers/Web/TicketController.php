<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Repositories\TicketRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketRepository $ticketRepository
    ) {
    }

    /**
     * Отобразить список заявок с фильтрацией
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'date_from', 'date_to', 'email', 'phone']);
        
        // Убираем пустые значения
        $filters = array_filter($filters, fn($value) => $value !== null && $value !== '');

        $tickets = $this->ticketRepository->filter($filters);

        $statuses = [
            Ticket::STATUS_NEW => 'Новая',
            Ticket::STATUS_IN_PROGRESS => 'В работе',
            Ticket::STATUS_COMPLETED => 'Обработана',
        ];

        return view('admin.tickets.index', [
            'tickets' => $tickets,
            'statuses' => $statuses,
            'filters' => $filters,
        ]);
    }
}
