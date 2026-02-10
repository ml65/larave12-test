<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    /**
     * Авторизация и получение токена
     */
    #[OA\Post(
        path: '/api/login',
        summary: 'Авторизация менеджера',
        description: 'Авторизация менеджера и получение токена для доступа к защищенным эндпойнтам',
        tags: ['Авторизация'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'manager@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешная авторизация',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string', example: '1|abcdef1234567890...'),
                        new OA\Property(
                            property: 'user',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Manager'),
                                new OA\Property(property: 'email', type: 'string', example: 'manager@example.com'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Неверные учетные данные',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Invalid credentials.'),
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
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $user = Auth::user();

        // Проверяем роль менеджера
        if (!$user->hasRole('manager')) {
            Auth::logout();

            return response()->json([
                'message' => 'Access denied. Manager role required.',
            ], 403);
        }

        // Создаем токен
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Выход и удаление токена
     */
    #[OA\Post(
        path: '/api/logout',
        summary: 'Выход из системы',
        description: 'Выход и удаление текущего токена. Требует авторизации.',
        tags: ['Авторизация'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешный выход',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Logged out successfully.'),
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
        ]
    )]
    public function logout(): JsonResponse
    {
        $user = Auth::user();

        if ($user && $user->currentAccessToken()) {
            // Удаляем текущий токен (только если это PersonalAccessToken, не TransientToken)
            $token = $user->currentAccessToken();
            if (method_exists($token, 'delete')) {
                $token->delete();
            }
        }

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
