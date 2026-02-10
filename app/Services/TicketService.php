<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ticket;
use App\Repositories\TicketRepository;

class TicketService extends BaseService
{
    private const MAX_TICKETS_PER_DAY = 1;

    public function __construct(
        private readonly TicketRepository $ticketRepository,
        private readonly CustomerService $customerService
    ) {
    }

    /**
     * Создать заявку
     *
     * @throws \RuntimeException Если превышен лимит заявок (1 в день)
     */
    public function create(array $data): Ticket
    {
        // Проверяем лимит: не более 1 заявки в день с одного контакта (по номеру и email)
        $phone = $data['phone'] ?? null;
        $email = $data['email'] ?? null;

        if ($phone === null) {
            throw new \InvalidArgumentException('Phone is required');
        }

        // Проверяем лимит заявок за текущий день (с 0:00:00 до 23:59:59)
        $todayTicketsCount = $this->ticketRepository->countTicketsTodayByContact($phone, $email);

        if ($todayTicketsCount >= self::MAX_TICKETS_PER_DAY) {
            throw new \RuntimeException('Only one ticket per day is allowed from the same contact');
        }

        // Находим или создаем клиента
        $customer = $this->customerService->findOrCreate([
            'name' => $data['name'] ?? '',
            'phone' => $phone,
            'email' => $email,
        ]);

        // Создаем заявку
        return $this->ticketRepository->create([
            'customer_id' => $customer->id,
            'subject' => $data['subject'] ?? '',
            'text' => $data['text'] ?? '',
            'status' => Ticket::STATUS_NEW,
            'manager_response_date' => null,
        ]);
    }

    /**
     * Изменить статус заявки
     *
     * @throws \InvalidArgumentException Если статус невалидный
     */
    public function updateStatus(int $ticketId, string $status): Ticket
    {
        $validStatuses = [
            Ticket::STATUS_NEW,
            Ticket::STATUS_IN_PROGRESS,
            Ticket::STATUS_COMPLETED,
        ];

        if (!in_array($status, $validStatuses, true)) {
            throw new \InvalidArgumentException("Invalid status: {$status}");
        }

        $ticket = $this->ticketRepository->find($ticketId);

        if ($ticket === null) {
            throw new \RuntimeException("Ticket with ID {$ticketId} not found");
        }

        // Обновляем статус
        $this->ticketRepository->update($ticket, [
            'status' => $status,
            'manager_response_date' => $status !== Ticket::STATUS_NEW ? now() : null,
        ]);

        return $ticket->fresh();
    }

    /**
     * Прикрепить файлы к заявке
     */
    public function attachFiles(Ticket $ticket, array $files): void
    {
        foreach ($files as $file) {
            $ticket->addMedia($file->getRealPath())
                ->usingName($file->getClientOriginalName())
                ->usingFileName($file->getClientOriginalName())
                ->toMediaCollection('attachments');
        }
    }
}
