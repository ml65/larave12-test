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
use OpenApi\Attributes as OA;

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
    #[OA\Post(
        path: '/api/tickets',
        summary: 'Создать заявку',
        description: 'Создает новую заявку. Если клиент с указанным телефоном не существует, он будет создан автоматически. Лимит: не более 1 заявки в день с одного контакта.',
        tags: ['Заявки'],
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        required: ['name', 'phone', 'subject', 'text'],
                        properties: [
                            new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Иван Иванов'),
                            new OA\Property(property: 'phone', type: 'string', pattern: '^\+[1-9]\d{1,14}$', example: '+79991234567', description: 'Телефон в формате E.164'),
                            new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true, example: 'ivan@example.com'),
                            new OA\Property(property: 'subject', type: 'string', maxLength: 255, example: 'Вопрос по услугам'),
                            new OA\Property(property: 'text', type: 'string', example: 'Хочу узнать больше о ваших услугах'),
                        ]
                    )
                ),
                new OA\MediaType(
                    mediaType: 'multipart/form-data',
                    schema: new OA\Schema(
                        required: ['name', 'phone', 'subject', 'text'],
                        properties: [
                            new OA\Property(property: 'name', type: 'string', example: 'Иван Иванов'),
                            new OA\Property(property: 'phone', type: 'string', example: '+79991234567'),
                            new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true, example: 'ivan@example.com'),
                            new OA\Property(property: 'subject', type: 'string', example: 'Вопрос по услугам'),
                            new OA\Property(property: 'text', type: 'string', example: 'Хочу узнать больше о ваших услугах'),
                            new OA\Property(property: 'files', type: 'array', items: new OA\Items(type: 'string', format: 'binary'), description: 'Массив файлов (максимум 10MB на файл)'),
                        ]
                    )
                ),
            ]
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Заявка успешно создана',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'subject', type: 'string', example: 'Вопрос по услугам'),
                        new OA\Property(property: 'text', type: 'string', example: 'Хочу узнать больше о ваших услугах'),
                        new OA\Property(property: 'status', type: 'string', example: 'new'),
                        new OA\Property(
                            property: 'customer',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Иван Иванов'),
                                new OA\Property(property: 'phone', type: 'string', example: '+79991234567'),
                                new OA\Property(property: 'email', type: 'string', example: 'ivan@example.com'),
                            ]
                        ),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-15T10:30:00.000000Z'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка валидации данных',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Phone is required'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Ошибки валидации',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'phone', type: 'array', items: new OA\Items(type: 'string'), example: ['The phone number must be in E.164 format (e.g., +1234567890).']),
                                new OA\Property(property: 'name', type: 'array', items: new OA\Items(type: 'string'), example: ['The name field is required.']),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 429,
                description: 'Превышен лимит заявок (1 в день)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Only one ticket per day is allowed from the same contact'),
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Внутренняя ошибка сервера',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Failed to create ticket. Please try again later.'),
                    ]
                )
            ),
        ]
    )]
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
    #[OA\Get(
        path: '/api/tickets/statistics',
        summary: 'Получить статистику по заявкам',
        description: 'Возвращает статистику по заявкам (дневная, недельная, месячная). Доступно только для авторизованных менеджеров.',
        tags: ['Заявки'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Статистика по заявкам',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'daily', type: 'integer', example: 5, description: 'Количество заявок за сегодня'),
                                new OA\Property(property: 'weekly', type: 'integer', example: 12, description: 'Количество заявок за текущую неделю'),
                                new OA\Property(property: 'monthly', type: 'integer', example: 45, description: 'Количество заявок за текущий месяц'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Не авторизован',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Доступ запрещен. Требуется роль менеджера',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Access denied. Manager role required.'),
                    ]
                )
            ),
        ]
    )]
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
