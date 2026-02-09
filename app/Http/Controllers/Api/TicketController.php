<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Resources\TicketResource;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketService $ticketService
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
            
            // Обрабатываем файлы через медиа-библиотеку
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $ticket->addMedia($file->getRealPath())
                        ->usingName($file->getClientOriginalName())
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('attachments');
                }
            }
            
            return new TicketResource($ticket);
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
}
