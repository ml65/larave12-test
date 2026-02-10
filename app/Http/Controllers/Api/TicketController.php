<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Resources\TicketResource;
use App\Http\Resources\TicketStatisticsResource;
use App\Repositories\TicketRepository;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketService $ticketService,
        private readonly TicketRepository $ticketRepository
    ) {
    }

    /**
     * Создать новую заявку
     */
    public function store(StoreTicketRequest $request): JsonResponse|TicketResource
    {
        try {
            $validated = $request->validated();
            
            // Создаем заявку через сервис
            $ticket = $this->ticketService->create($validated);
            
            // Загружаем связь с клиентом
            $ticket->load('customer');
            
            // Обрабатываем файлы через сервис
            if ($request->hasFile('files')) {
                $this->ticketService->attachFiles($ticket, $request->file('files'));
            }
            
            return new TicketResource($ticket);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        } catch (\RuntimeException $e) {
            // Обработка ошибки лимита заявок
            Log::warning('Ticket creation limit exceeded', [
                'phone' => $request->input('phone'),
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'message' => $e->getMessage(),
            ], 429); // Too Many Requests
        } catch (\Exception $e) {
            // Обработка других ошибок
            Log::error('Ticket creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'message' => 'Failed to create ticket. Please try again later.',
            ], 500);
        }
    }

    /**
     * Получить статистику по заявкам
     */
    public function statistics(): TicketStatisticsResource|JsonResponse
    {
        // Проверка роли менеджера (авторизация уже проверена через middleware)
        if (!auth()->user()->hasRole('manager')) {
            return response()->json([
                'message' => 'Access denied. Manager role required.',
            ], 403);
        }

        $statistics = [
            'daily' => $this->ticketRepository->getDailyCount(),
            'weekly' => $this->ticketRepository->getWeeklyCount(),
            'monthly' => $this->ticketRepository->getMonthlyCount(),
        ];

        return new TicketStatisticsResource($statistics);
    }
}
