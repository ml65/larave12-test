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

        return view('admin.tickets.index', [
            'tickets' => $tickets,
            'statuses' => Ticket::getStatusLabels(),
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

        return view('admin.tickets.show', [
            'ticket' => $ticket,
            'files' => $files,
            'statuses' => Ticket::getStatusLabels(),
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
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('admin.tickets.show', $id)
                ->with('error', 'Неверный статус: ' . $e->getMessage());
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('admin.tickets.show', $id)
                ->with('error', 'Заявка не найдена: ' . $e->getMessage());
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.tickets.show', $id)
                ->with('error', 'Ошибка при обновлении статуса: ' . $e->getMessage());
        }
    }
}
