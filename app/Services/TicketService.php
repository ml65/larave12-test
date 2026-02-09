<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ticket;
use App\Repositories\TicketRepository;
use Illuminate\Support\Carbon;

class TicketService extends BaseService
{
    public function __construct(
        private readonly TicketRepository $ticketRepository,
        private readonly CustomerService $customerService
    ) {
    }

    /**
     * Создать заявку
     *
     * @throws \RuntimeException Если превышен лимит заявок (более 1 в день с одного контакта)
     */
    public function create(array $data): Ticket
    {
        // Находим или создаем клиента
        $customer = $this->customerService->findOrCreate([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
        ]);

        // Проверяем лимит: не более 1 заявки в день с одного контакта
        $todayTickets = $this->ticketRepository->findByCustomerId($customer->id)
            ->filter(function (Ticket $ticket) {
                return $ticket->created_at->isToday();
            });

        if ($todayTickets->count() > 0) {
            throw new \RuntimeException('Превышен лимит: не более одной заявки в день с одного контакта');
        }

        // Создаем заявку
        return $this->ticketRepository->create([
            'customer_id' => $customer->id,
            'subject' => $data['subject'],
            'text' => $data['text'],
            'status' => Ticket::STATUS_NEW,
            'manager_response_date' => null,
        ]);
    }

    /**
     * Изменить статус заявки
     *
     * @throws \InvalidArgumentException Если статус невалиден
     */
    public function updateStatus(int $ticketId, string $status): Ticket
    {
        $validStatuses = [
            Ticket::STATUS_NEW,
            Ticket::STATUS_IN_PROGRESS,
            Ticket::STATUS_COMPLETED,
        ];

        if (!in_array($status, $validStatuses, true)) {
            throw new \InvalidArgumentException("Невалидный статус: {$status}");
        }

        $ticket = $this->ticketRepository->find($ticketId);

        if ($ticket === null) {
            throw new \RuntimeException("Заявка с ID {$ticketId} не найдена");
        }

        $updateData = ['status' => $status];

        // Если статус меняется на "в работе" или "обработан", устанавливаем дату ответа
        if (in_array($status, [Ticket::STATUS_IN_PROGRESS, Ticket::STATUS_COMPLETED], true)) {
            $updateData['manager_response_date'] = Carbon::now();
        }

        $this->ticketRepository->update($ticket, $updateData);

        return $ticket->fresh();
    }
}

