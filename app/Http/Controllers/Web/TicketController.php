<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateTicketStatusRequest;
use App\Models\Ticket;
use App\Repositories\TicketRepository;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketRepository $ticketRepository,
        private readonly TicketService $ticketService
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

    /**
     * Отобразить детали заявки
     */
    public function show(int $id): View
    {
        $ticket = $this->ticketRepository->find($id);

        if ($ticket === null) {
            abort(404, 'Заявка не найдена');
        }

        $ticket->load('customer');
        $files = $ticket->getMedia('attachments');

        $statuses = [
            Ticket::STATUS_NEW => 'Новая',
            Ticket::STATUS_IN_PROGRESS => 'В работе',
            Ticket::STATUS_COMPLETED => 'Обработана',
        ];

        return view('admin.tickets.show', [
            'ticket' => $ticket,
            'files' => $files,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Изменить статус заявки
     */
    public function updateStatus(UpdateTicketStatusRequest $request, int $id): RedirectResponse
    {
        try {
            $this->ticketService->updateStatus($id, $request->validated()['status']);

            return redirect()
                ->route('admin.tickets.show', $id)
                ->with('success', 'Статус заявки успешно обновлен');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.tickets.show', $id)
                ->with('error', 'Ошибка при обновлении статуса: ' . $e->getMessage());
        }
    }
}
