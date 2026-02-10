<?php

declare(strict_types=1);

namespace App\Http\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Mini-CRM API',
    description: 'API для системы управления заявками. Позволяет создавать заявки через виджет и получать статистику для менеджеров.',
    contact: new OA\Contact(
        email: 'support@example.com'
    ),
    license: new OA\License(
        name: 'MIT'
    )
)]
#[OA\Server(
    url: '/',
    description: 'Основной сервер'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Используйте токен, полученный при авторизации через /api/login'
)]
class OpenApiInfo
{
}

